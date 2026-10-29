<?php

namespace App\Support;

use App\Models\ContentField;
use App\Models\ContentList;
use App\Models\MenuGroup;
use App\Models\Page;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Loads the seed files in database/seeders/content (produced from the original PHP site by tools/convert_legacy.php)
 * into MySQL. By default nothing that already exists is touched, so re-running it never overwrites admin edits;
 * pass $force = true to reset content back to the original text.
 */
class ContentImporter
{
    private const ACRONYMS = ['Cta' => 'CTA', 'Faqs' => 'FAQs', 'Seo' => 'SEO', 'Ppt' => 'PPT', 'Dtp' => 'DTP', 'Iso' => 'ISO'];

    /** @param list<string>|null $only  file names (page slugs) to import; null = everything */
    public function __construct(private bool $force = false, private ?array $only = null) {}

    public function run(): array
    {
        $stats = ['pages' => 0, 'fields' => 0, 'lists' => 0, 'menus' => 0, 'settings' => 0];

        DB::transaction(function () use (&$stats) {
            $files = glob(database_path('seeders/content/*.json')) ?: [];
            sort($files);
            $sort = 0;
            foreach ($files as $file) {
                $name = basename($file, '.json');
                if ($name === '_shared' || ($this->only !== null && !in_array($name, $this->only, true))) {
                    continue;
                }
                $seed = json_decode(file_get_contents($file), true);
                if (!empty($seed['page'])) {
                    $stats['pages'] += $this->page($seed['page'], ++$sort);
                    $scope = $seed['page']['slug'];
                } else {
                    $scope = 'global';
                }
                $stats['fields'] += $this->fields($scope, $seed['fields'] ?? []);
                $stats['lists'] += $this->lists($scope, $seed['lists'] ?? []);
            }

            if ($this->only !== null) {
                return;                    // a single page was asked for: leave menus, shared copy and settings alone
            }
            if (is_file($shared = database_path('seeders/content/_shared.json'))) {
                $stats['menus'] += $this->shared(json_decode(file_get_contents($shared), true));
            }
            $stats['fields'] += $this->globalCopy();
            $stats['pages'] += $this->starterPages();
            $stats['settings'] += $this->settings();
        });

        foreach (array_merge(['global'], Page::pluck('slug')->all()) as $scope) {
            Content::forget($scope);
        }
        SiteMenus::flush();
        SiteSetting::flush();

        return $stats;
    }

    private function page(array $p, int $sort): int
    {
        $slug = $p['slug'];
        $full = $p['config']['title'] ?? $slug;
        $short = $slug === 'index' ? 'Home' : trim(Str::before($full, '|'));
        $page = Page::withTrashed()->firstOrNew(['slug' => $slug]);
        if ($page->exists && !$this->force) {
            return 0;
        }
        $page->slug = $slug;
        $sortValue = $page->exists && $this->only !== null ? $page->sort : ($p['is_system'] ?? true ? $sort : (int) Page::max('sort') + 1);
        $page->fill(['title' => $short, 'meta_title' => $full, 'status' => Page::STATUS_PUBLISHED, 'sort' => $sortValue]);
        if (!empty($p['meta_description'])) {
            $page->meta_description = $p['meta_description'];
        }
        if (!empty($p['meta_keywords'])) {
            $page->meta_keywords = $p['meta_keywords'];
        }
        $page->template = $p['template'] ?? $slug;     // a page can reuse the design of another page
        $page->is_system = $p['is_system'] ?? true;
        $page->published_at ??= now();
        $page->deleted_at = null;
        $page->save();
        return 1;
    }

    private function fields(string $scope, array $fields): int
    {
        $n = 0;
        foreach ($fields as $f) {
            $row = ContentField::firstOrNew(['scope' => $scope, 'key' => $f['key']]);
            if ($row->exists && !$this->force) {
                continue;
            }
            $row->scope = $scope;
            $row->key = $f['key'];
            $row->fill(['value' => $f['value']]);
            $row->section = $this->sectionLabel($f['section_label'] ?? $f['section'] ?? 'General');
            $row->label = Str::limit($f['label'], 250, '…');
            $row->type = $f['type'];
            $row->sort = $f['sort'];
            $row->save();
            $n++;
        }
        return $n;
    }

    private function lists(string $defaultScope, array $lists): int
    {
        $n = 0;
        foreach ($lists as $l) {
            $scope = $l['scope'] ?? $defaultScope;
            $row = ContentList::firstOrNew(['scope' => $scope, 'key' => $l['key']]);
            if ($row->exists && !$this->force) {
                continue;
            }
            $meta = config('content_lists.' . $l['key'], []);
            $row->scope = $scope;
            $row->key = $l['key'];
            $row->items = $l['items'];
            $row->label = $meta['label'] ?? Str::headline($l['key']);
            $row->section = $this->sectionLabel($meta['section'] ?? 'Lists');
            $row->sort = $meta['sort'] ?? 100;
            $row->save();
            $n++;
        }
        return $n;
    }

    private function sectionLabel(string $label): string
    {
        $label = preg_replace_callback('/\b[A-Za-z]+\b/', fn ($m) => self::ACRONYMS[$m[0]] ?? $m[0], $label);
        return Str::limit($label, 110, '');
    }

    /** menus + offices from tools/export_shared.php */
    private function shared(array $data): int
    {
        $n = 0;
        foreach ($data['menus'] ?? [] as $location => $groups) {
            foreach ($groups as $i => $g) {
                $query = MenuGroup::where('location', $location);
                $group = $location === 'footer' ? $query->where('handle', $g['handle'])->first() : $query->where('title', $g['title'])->first();
                if ($group && !$this->force) {
                    continue;
                }
                $group ??= new MenuGroup(['location' => $location]);
                $group->forceFill(['location' => $location, 'handle' => $g['handle'] ?? null, 'title' => $g['title'], 'sort' => $i + 1, 'is_published' => true])->save();
                $group->items()->delete();
                foreach ($g['items'] as $j => $item) {
                    $group->items()->create(['label' => $item['label'], 'url' => $item['url'], 'sort' => $j + 1, 'is_published' => true]);
                }
                $n++;
            }
        }

        if (!empty($data['offices'])) {
            $row = ContentList::firstOrNew(['scope' => 'global', 'key' => 'offices']);
            if (!$row->exists || $this->force) {
                $row->scope = 'global';
                $row->key = 'offices';
                $row->items = $data['offices'];
                $row->label = config('content_lists.offices.label', 'Offices');
                $row->section = config('content_lists.offices.section', 'Offices');
                $row->sort = 1;
                $row->save();
                $n++;
            }
        }
        return $n;
    }

    /**
     * Ordinary text pages that the menus link to (industries, legal pages, insights). They are created once and then
     * belong to the editor: the importer never overwrites them.
     */
    private function starterPages(): int
    {
        $note = '<p>This page is ready to be filled in from the admin panel (Pages &rarr; edit). Until then, tell us what you are looking for and our team will get back to you.</p>';
        $pages = [
            ['industries', 'Industries', $note],
            ['industrial-engineering', 'Industrial & Engineering', $note],
            ['b2b-services', 'B2B Services', $note],
            ['retail-ecommerce', 'Retail & eCommerce', $note],
            ['education', 'Education', $note],
            ['startups-saas', 'Startups & SaaS', $note],
            ['privacy-policy', 'Privacy Policy', '<p>Our privacy policy explains what information we collect through this website, how we use it and how you can reach us about it. The full text will be published here.</p>'],
            ['terms-of-use', 'Terms of Use', '<p>These terms describe how this website may be used. The full text will be published here.</p>'],
            ['cookies', 'Cookies', '<p>This page explains the cookies used on this website and how to control them. The full text will be published here.</p>'],
            ['blog', 'Insights', $note],
        ];
        $n = 0;
        foreach ($pages as [$slug, $title, $body]) {
            if (Page::withTrashed()->where('slug', $slug)->exists()) {
                continue;
            }
            $page = new Page();
            $page->slug = $slug;
            $page->template = 'generic';
            $page->is_system = false;
            $page->fill(['title' => $title, 'meta_title' => $title . ' | Intelliworkz', 'content' => $body, 'status' => Page::STATUS_PUBLISHED, 'sort' => (int) Page::max('sort') + 1]);
            $page->published_at = now();
            $page->save();
            $n++;
        }
        return $n;
    }

    /** Copy printed by the hand-written header / footer partials (they are not converted from PHP). */
    private function globalCopy(): int
    {
        $fields = [
            ['Header', 'header.1', 'Menu button label', 'text', 'Menu'],
            ['Header', 'header.2', 'Top-bar button text', 'text', 'Start a Project'],
            ['Header', 'header.3', 'Top-bar button link', 'url', 'contact-us.php'],
            ['Header', 'header.4', 'Menu panel button text', 'text', 'Start a Project'],
            ['Footer call-to-action', 'footer.1', 'Heading', 'text', 'Have a Business Challenge? Let\'s Work on It.'],
            ['Footer call-to-action', 'footer.2', 'Paragraph', 'html', 'Tell us what you\'re trying to build, improve, market, or manage. We\'ll understand the requirements, identify the right capabilities, and work with you on the way forward.<br>Whether you need a new digital experience, a technology solution, stronger brand communication, or additional execution capacity, let\'s start with the requirements.'],
            ['Footer call-to-action', 'footer.3', 'First button text', 'text', 'Start a Project'],
            ['Footer call-to-action', 'footer.4', 'First button link', 'url', 'contact-us.php'],
            ['Footer call-to-action', 'footer.5', 'Second button text', 'text', 'Explore Our Services'],
            ['Footer call-to-action', 'footer.6', 'Second button link', 'url', 'services.php'],
            ['Footer', 'footer.7', 'Certification text', 'html', '<strong>ISO 27001</strong><br>Certified'],
            ['Enquiry forms', 'form.1', 'Privacy note under the form button', 'text', 'We respect your privacy. Your information is safe with us.'],
            ['Enquiry forms', 'form.2', 'Subject box hint', 'text', 'Enter your subject'],
            ['Enquiry forms', 'form.3', 'Message box hint', 'text', 'Tell us about your requirements'],
            ['Case study cards', 'showcase.1', 'Button text', 'text', 'View Case Study'],
            ['Case study cards', 'showcase.2', 'Button link', 'url', 'case-study.php'],
            ['Contact page offices', 'contact.1', 'Map button text', 'text', 'View on Map ↗'],
            ['Contact page offices', 'contact.2', 'Call button text', 'text', 'Call Office →'],
            ['Pages made in the admin', 'page.1', 'Breadcrumb: first word', 'text', 'Home'],
            ['Thank-you page', 'thanks.1', 'Heading (the sender\'s first name is added)', 'text', 'Thank you'],
            ['Thank-you page', 'thanks.2', 'Text', 'textarea', 'Your message has reached us. Our team will read it and get back to you shortly, usually within one working day.'],
            ['Thank-you page', 'thanks.3', 'First button text', 'text', 'Back to home'],
            ['Thank-you page', 'thanks.4', 'Second button text', 'text', 'Explore our services'],
            ['Thank-you page', 'thanks.5', 'Second button link', 'url', 'services.php'],
            ['Thank-you page', 'thanks.6', 'Heading above the page links', 'text', 'While you are here'],
            ['Page not found', 'notfound.1', 'Heading', 'text', 'We can\'t find that page'],
            ['Page not found', 'notfound.2', 'Text', 'textarea', 'The address may be mistyped, or the page has moved or is no longer available.'],
            ['Page not found', 'notfound.3', 'First button text', 'text', 'Back to home'],
            ['Page not found', 'notfound.4', 'Second button text', 'text', 'Contact us'],
            ['Page not found', 'notfound.5', 'Second button link', 'url', 'contact-us.php'],
            ['Page not found', 'notfound.6', 'Heading above the page links', 'text', 'Maybe you were looking for'],
        ];
        $n = 0;
        foreach ($fields as $i => [$section, $key, $label, $type, $value]) {
            $row = ContentField::firstOrNew(['scope' => 'global', 'key' => $key]);
            if ($row->exists && !$this->force) {
                continue;
            }
            $row->scope = 'global';
            $row->key = $key;
            $row->fill(['value' => $value]);
            $row->section = $section;
            $row->label = $label;
            $row->type = $type;
            $row->sort = $i + 1;
            $row->save();
            $n++;
        }
        return $n;
    }

    private function settings(): int
    {
        $legacy = is_file($f = database_path('seeders/content/_shared.json')) ? json_decode(file_get_contents($f), true) : [];
        $rows = [
            ['company', 'company_name', 'Company name', 'text', 'Intelliworkz', 'Used in the logo alt text and the page title suffix.'],
            ['company', 'copyright', 'Copyright line', 'text', $legacy['site']['copyright'] ?? 'Copyright © ' . date('Y') . ' Intelliworkz Business Solutions Pvt. Ltd. All Rights Reserved.', 'Shown at the very bottom of every page.'],
            ['company', 'footer_about', 'Footer description', 'textarea', 'Lorem ipsum dolor sit amet consectetur. Morbi feugiat donec felis ac aenean eget in. Facilisis sed massa eleifend aliquet aliquet leo. Commodo sit a dictum diam et nulla. Volutpat ut lectus pulvinar vel leo id at duis. Aliquet nunc dolor ultricies enim lacinia. Viverra volutpat.', 'The paragraph under the logo in the footer.'],
            ['branding', 'logo_header', 'Header logo', 'image', 'assets/icons/logo.svg', 'Shown in the top bar and the menu panel. SVG or PNG, on a dark background.'],
            ['branding', 'logo_footer', 'Footer logo', 'image', 'assets/icons/footer-logo.svg', 'Shown in the footer on a light background.'],
            ['branding', 'favicon', 'Favicon', 'image', 'assets/images/favicon.png', 'Square PNG, at least 100×100 px.'],
            ['branding', 'iso_image', 'Certification badge', 'image', 'assets/icons/iso.svg', 'The ISO badge in the footer.'],
            ['contact', 'contact_email', 'Inquiry notification e-mail', 'email', null, 'Optional. New inquiries are listed in the admin panel; add an address here to also receive them by e-mail.'],
            ['contact', 'contact_phone', 'Main phone number', 'text', null, null],
            ['social', 'social_facebook', 'Facebook', 'url', '', 'Full address, e.g. https://facebook.com/yourpage'],
            ['social', 'social_instagram', 'Instagram', 'url', '', null],
            ['social', 'social_linkedin', 'LinkedIn', 'url', '', null],
            ['seo', 'seo_description', 'Default meta description', 'textarea', null, 'Used by pages that do not have their own description (about 150 characters).'],
            ['seo', 'seo_image', 'Default share image', 'image', null, 'Shown when a page is shared on social media (1200×630 px works best).'],
        ];
        $n = 0;
        foreach ($rows as $i => [$group, $key, $label, $type, $value, $help]) {
            $row = SiteSetting::firstOrNew(['key' => $key]);
            if ($row->exists && !$this->force) {
                continue;
            }
            $row->key = $key;
            $row->group = $group;
            $row->label = $label;
            $row->type = $type;
            $row->help = $help;
            $row->sort = $i + 1;
            $row->value = $value;
            $row->save();
            $n++;
        }
        return $n;
    }
}
