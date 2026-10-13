<?php

namespace Tests\Feature;

use App\Models\Page;
use Tests\CmsTestCase;

/** Every button / link on every public page leads somewhere, and every admin screen opens. */
class LinksAndAdminTest extends CmsTestCase
{
    public function test_every_link_on_every_public_page_works(): void
    {
        $checked = [];
        foreach (Page::published()->get() as $page) {
            $html = $this->get($page->slug === 'index' ? '/' : '/' . $page->slug . ($page->is_system ? '.php' : ''))->assertOk()->getContent();
            preg_match_all('/<a\b[^>]*\bhref="([^"]*)"[^>]*>(.*?)<\/a>/is', $html, $m, PREG_SET_ORDER);
            foreach ($m as [$tag, $href, $inner]) {
                $href = html_entity_decode($href);
                $this->assertNotContains($href, ['#', ''], "A dead link “" . trim(strip_tags($inner)) . "” on {$page->slug}");
                if (preg_match('#^(mailto:|tel:|https?://)#', $href)) {
                    continue;
                }
                $path = '/' . ltrim(preg_replace('/#.*$/', '', $href), '/');
                if ($path === '/' && !str_starts_with($href, '/')) {
                    continue;                      // "#section" on the same page
                }
                if (isset($checked[$path])) {
                    continue;
                }
                $checked[$path] = true;
                $this->get($path)->assertSuccessful();
            }
        }
        $this->assertNotEmpty($checked);
    }

    public function test_every_admin_screen_opens(): void
    {
        $this->actingAs($this->admin());
        $screens = ['/admin', '/admin/pages', '/admin/pages/create', '/admin/shared', '/admin/menu', '/admin/settings', '/admin/inquiries', '/admin/activity', '/admin/profile', '/admin/users', '/admin/users/create'];
        foreach (Page::all() as $page) {
            $screens[] = "/admin/pages/{$page->slug}/edit";
            if ($page->hasEditor()) {
                $screens[] = "/admin/pages/{$page->slug}/content";
            }
        }
        foreach ($screens as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_the_card_numbers_can_be_switched_off_per_page_in_the_admin(): void
    {
        $this->get('/pre-press-service.php')->assertSee('pp-why__no', false);
        $this->get('/packaging-design.php')->assertSee('pk-why__no', false);

        $field = \App\Models\ContentField::where('scope', 'pre-press-service')->where('key', 'card-numbers.1')->firstOrFail();
        $this->actingAs($this->editor())->put('/admin/pages/pre-press-service/content', ['fields' => [$field->id => '0']])->assertSessionHasNoErrors();
        auth()->logout();

        $this->get('/pre-press-service.php')->assertOk()->assertDontSee('pp-why__no', false);
        $this->get('/packaging-design.php')->assertSee('pk-why__no', false);           // other pages keep their numbers
    }

    public function test_the_filter_tabs_can_be_switched_off_per_page_in_the_admin(): void
    {
        $this->get('/pre-press-service.php')->assertSee('class="filter-tabs"', false);

        $field = \App\Models\ContentField::where('scope', 'pre-press-service')->where('key', 'filter-tabs.1')->firstOrFail();
        $this->actingAs($this->editor())->put('/admin/pages/pre-press-service/content', ['fields' => [$field->id => '0']])->assertSessionHasNoErrors();
        auth()->logout();

        $this->get('/pre-press-service.php')->assertOk()->assertDontSee('class="filter-tabs"', false);
        $this->get('/seo-services.php')->assertSee('class="filter-tabs"', false);      // other pages keep their tabs
    }
}
