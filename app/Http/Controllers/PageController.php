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

        $view = $page->is_system ? 'site.pages.' . $page->slug : 'site.generic';
        abort_unless(view()->exists($view), 404);

        return response()->view($view, $this->data($page, $preview));
    }

    private function data(Page $page, bool $preview): array
    {
        $content = app(Content::class);
        $fields = $content->fields($page->slug);
        $globals = $content->fields('global');
        $settings = SiteSetting::map();
        $menus = SiteMenus::get();
        $cfg = config('site_pages.' . $page->slug, [
            'body_class' => 'page-generic', 'extra_css' => ['assets/css/generic.css'], 'no_zoom' => true,
            'show_prefooter' => true, 'footer_class' => '', 'phone_icon' => null,
        ]);

        $title = $page->metaTitle();
        $description = $page->meta_description ?: ($settings['seo_description'] ?? null);
        $image = Media::url($page->og_image ?: ($settings['seo_image'] ?? null));

        $lists = array_merge($content->lists('global'), $content->lists($page->slug));

        return $lists + [
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
