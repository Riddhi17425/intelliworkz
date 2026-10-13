<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\SiteSetting;
use App\Support\Content;
use App\Support\Html;
use App\Support\Media;
use App\Support\SiteMenus;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/** Renders the public pages from the database; the markup itself lives in resources/views/site. */
class PageController extends Controller
{
    public function show(Request $request, string $slug = 'index'): Response
    {
        $page = Page::where('slug', $slug)->first();
        abort_if(!$page, 404);

        // signed-in admins may look at drafts; everybody else gets a 404 until the page is published
        $preview = !$page->isPublished();
        abort_if($preview && !$request->user(), 404);

        $view = $page->hasEditor() ? 'site.pages.' . $page->viewKey() : 'site.generic';
        abort_unless(view()->exists($view), 404);

        return response()->view($view, $this->data($page, $preview));
    }

    /** Friendly 404 in the site design (called from the exception handler in bootstrap/app.php). */
    public function notFound(Request $request): Response
    {
        // an address that matches no route never went through the web middleware (no session, no $errors): give the layout what it expects
        if (!$request->hasSession()) {
            view()->share('errors', new \Illuminate\Support\ViewErrorBag());
        }

        $page = $this->messagePage('Page not found');
        $data = $this->data($page, false);
        $data['meta']['robots'] = 'noindex, nofollow';
        $data['meta']['canonical'] = url('/');

        return response()->view('site.message', $data + ['kind' => '404', 'name' => null, 'links' => $this->suggestions()], 404);
    }

    /** Shown after an enquiry has been sent (the form redirects here). */
    public function thanks(Request $request): Response
    {
        $page = $this->messagePage('Thank you');
        $data = $this->data($page, false);
        $data['meta']['robots'] = 'noindex, nofollow';
        $data['meta']['canonical'] = url('thank-you');

        $name = Html::plain((string) $request->session()->get('inquiry_name', ''));
        $first = $name !== '' ? \Illuminate\Support\Str::before($name, ' ') : null;

        return response()->view('site.message', $data + ['kind' => 'thanks', 'name' => $first ? \Illuminate\Support\Str::limit($first, 40, '') : null, 'links' => $this->suggestions()])
            ->header('Cache-Control', 'no-store');
    }

    private function messagePage(string $title): Page
    {
        $page = new Page(['title' => $title, 'status' => Page::STATUS_PUBLISHED]);
        $page->slug = 'message';
        $page->template = 'generic';
        $page->is_system = false;
        return $page;
    }

    /** A few published pages to send the visitor on to. */
    private function suggestions()
    {
        return Page::published()->where('slug', '!=', 'index')->where('slug', '!=', 'contact-us')->orderBy('sort')->orderBy('id')->limit(8)->get(['id', 'slug', 'title', 'is_system']);
    }

    private function data(Page $page, bool $preview): array
    {
        $content = app(Content::class);
        $fields = $content->fields($page->slug);
        $globals = $content->fields('global');
        $settings = SiteSetting::map();
        $menus = SiteMenus::get();
        $cfg = config('site_pages.' . ($page->viewKey() ?? 'generic'), [
            'body_class' => 'page-generic', 'extra_css' => ['assets/css/generic.css'], 'no_zoom' => false,
            'show_prefooter' => true, 'footer_class' => '', 'phone_icon' => null,
        ]);

        $title = $page->metaTitle();
        $description = $page->meta_description ?: ($settings['seo_description'] ?? null);
        $image = Media::url($page->og_image ?: ($settings['seo_image'] ?? null));

        $lists = array_merge($content->lists('global'), $content->lists($page->slug));

        // service designs are drawn section by section (order, visibility and copied-in sections come from the admin)
        $sections = [];
        if ($page->hasSections()) {
            $built = \App\Support\SectionPlan::plan($page, $content);
            $sections = ['sectionPlan' => $built['plan'], 'mainExtra' => $built['main']];
            $cfg['extra_css'] = array_values(array_unique(array_merge($cfg['extra_css'] ?? [], $built['css'])));
        }

        return $lists + $sections + [
            'page' => $page,
            'cfg' => $cfg,
            'preview' => $preview,
            'settings' => $settings,
            'headerMenu' => $menus['header'],
            'footerMenu' => $menus['footer'],
            // $T('hero.1') = this page's copy, $G('footer.1') = copy shared by every page
            'T' => fn (string $key, string $default = '') => $fields[$key] ?? $default,
            'G' => fn (string $key, string $default = '') => $globals[$key] ?? $default,
            'meta' => [
                'title' => $title,
                'description' => $description ? Html::plain($description) : null,
                'keywords' => $page->meta_keywords,
                'robots' => $preview ? 'noindex, nofollow' : null,
                'canonical' => $page->url(),
                'image' => $image,
            ],
        ];
    }
}
