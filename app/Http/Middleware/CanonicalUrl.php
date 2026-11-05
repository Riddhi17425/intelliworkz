<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * One address per page, like any real website: http:// goes to https:// and www.domain goes to domain (or the other
 * way round, whatever APP_URL says). Only runs on the live site (APP_ENV=production) and only for the site's own domain,
 * so localhost, an IP address or a staging sub-domain are never redirected.
 */
class CanonicalUrl
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!app()->isProduction() || !in_array($request->method(), ['GET', 'HEAD'], true)) {
            return $next($request);
        }

        $target = parse_url((string) config('app.url'));
        $targetHost = strtolower($target['host'] ?? '');
        $targetScheme = $target['scheme'] ?? 'http';
        if ($targetHost === '' || filter_var($targetHost, FILTER_VALIDATE_IP) || $targetHost === 'localhost') {
            return $next($request);
        }

        $host = strtolower($request->getHost());
        $strip = fn (string $h) => preg_replace('/^www\./', '', $h);
        if ($host !== $targetHost && $strip($host) !== $strip($targetHost)) {
            return $next($request);   // some other domain pointing here: leave it alone
        }

        // behind a proxy / CDN the https flag arrives in a header
        $secure = $request->isSecure() || strtolower((string) $request->header('X-Forwarded-Proto')) === 'https';
        $wrongScheme = $targetScheme === 'https' && !$secure;

        if ($wrongScheme || $host !== $targetHost) {
            $port = isset($target['port']) ? ':' . $target['port'] : '';
            return redirect()->to($targetScheme . '://' . $targetHost . $port . $request->getRequestUri(), 301);
        }

        return $next($request);
    }
}
