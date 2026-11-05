<?php

namespace App\Support;

use Illuminate\Http\Response;

class RootUrls
{
    public static function fix(Response $response, string $basePath): Response
    {
        $type = (string) $response->headers->get('Content-Type');
        if ($type !== '' && !str_contains($type, 'html')) {
            return $response;
        }
        $response->setContent(self::html((string) $response->getContent(), $basePath));
        return $response;
    }

    public static function html(string $html, string $basePath): string
    {
        $root = rtrim($basePath, '/') . '/';

        // links to this same site are written root-based ("/contact-us", "/inquiries"), not "https://domain/contact-us",
        // so they keep working on www and non-www, on http and https, and when the domain changes
        $hosts = [];
        foreach ([request()->getSchemeAndHttpHost(), (string) config('app.url')] as $url) {
            $p = parse_url($url);
            if (!empty($p['host'])) {
                $hosts[] = preg_quote($p['host'] . (isset($p['port']) ? ':' . $p['port'] : ''), '~');
            }
        }
        if ($hosts) {
            $hostRe = '(?:https?:)?//(?:www\.)?(?:' . implode('|', array_unique($hosts)) . ')';
            $html = preg_replace_callback('~<(?:a|img|form|source|video|audio)\b[^>]*>~i', function ($tag) use ($hostRe, $basePath) {
                return preg_replace_callback('~\b(href|src|action|poster)=(["\'])' . $hostRe . '(/[^"\']*)?\2~i', function ($m) use ($basePath) {
                    $path = $m[3] ?? '';
                    return $m[1] . '=' . $m[2] . (rtrim($basePath, '/') . ($path === '' ? '/' : $path)) . $m[2];
                }, $tag[0]);
            }, $html);
        }

        // a value is "relative" when it has no scheme, no leading slash, and is not an in-page anchor, query or Blade leftover
        $relative = '(?!\/|#|\?|[a-z][a-z0-9+.\-]*:)';

        // src / href / poster / data-* attributes
        $html = preg_replace_callback(
            '/\b(src|href|poster|data-src|data-bg|data-poster|data-video|data-href)=(["\'])' . $relative . '([^"\']+)\2/i',
            fn ($m) => $m[1] . '=' . $m[2] . $root . $m[3] . $m[2],
            $html
        );

        // srcset="a.png 1x, b.png 2x"
        $html = preg_replace_callback('/\bsrcset=(["\'])([^"\']+)\1/i', function ($m) use ($root) {
            $parts = array_map(function ($candidate) use ($root) {
                $candidate = trim($candidate);
                return preg_match('/^(\/|#|[a-z][a-z0-9+.\-]*:)/i', $candidate) ? $candidate : $root . $candidate;
            }, explode(',', $m[2]));
            return 'srcset=' . $m[1] . implode(', ', $parts) . $m[1];
        }, $html);

        // page links lose the old ".php" (contact-us.php -> /contact-us, index.php -> /); other sites' links are left alone
        $host = request()->getSchemeAndHttpHost();
        $html = preg_replace_callback('/\bhref=(["\'])([^"\'#?]*?)\.php([#?][^"\']*)?\1/i', function ($m) use ($host) {
            $path = $m[2];
            if (preg_match('#^([a-z][a-z0-9+.\-]*:)?//#i', $path) || preg_match('#^[a-z][a-z0-9+.\-]*:#i', $path)) {
                if (stripos($path, $host . '/') !== 0) {
                    return $m[0];
                }
            }
            if (preg_match('#(^|/)index$#', $path)) {
                $path = preg_replace('#index$#', '', $path);
                $path = $path === '' ? '/' : $path;
            }
            return 'href=' . $m[1] . $path . ($m[3] ?? '') . $m[1];
        }, $html);

        // inline style="background: url(assets/...)"
        return preg_replace('/url\((["\']?)(?=(?:assets|uploads)\/)/i', 'url($1' . $root, $html);
    }
}
