<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\Http\Response;

/** robots.txt and sitemap.xml, built from the published pages so they never go out of date. */
class SeoController extends Controller
{
    public function robots(): Response
    {
        $lines = [
            'User-agent: *',
            'Allow: /',
            'Disallow: /admin',
            'Disallow: /thank-you',
            'Disallow: /inquiries',
            '',
            'Sitemap: ' . url('sitemap.xml'),
            '',
        ];
        return response(implode("\n", $lines), 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }

    public function sitemap(): Response
    {
        $pages = Page::published()->orderBy('sort')->orderBy('id')->get(['id', 'slug', 'template', 'is_system', 'updated_at', 'published_at']);

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        foreach ($pages as $page) {
            $isHome = $page->slug === 'index';
            $modified = ($page->updated_at ?: $page->published_at);
            $xml .= '  <url><loc>' . e($page->url()) . '</loc>'
                . ($modified ? '<lastmod>' . $modified->toAtomString() . '</lastmod>' : '')
                . '<changefreq>' . ($isHome || $page->slug === 'blog' ? 'weekly' : 'monthly') . '</changefreq>'
                . '<priority>' . ($isHome ? '1.0' : ($page->isBlog() ? '0.6' : '0.8')) . '</priority></url>' . "\n";
        }
        $xml .= '</urlset>' . "\n";

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }
}
