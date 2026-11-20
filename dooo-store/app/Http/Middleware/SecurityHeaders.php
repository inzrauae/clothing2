<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * Adds browser security headers to every page:
 *  - Content-Security-Policy: only this site's own scripts (plus inline scripts carrying
 *    the per-request nonce) may run, so injected <script> tags are blocked.
 *  - Clickjacking, MIME-sniffing, referrer and permissions protection.
 *  - HSTS when the site is served over HTTPS.
 */
class SecurityHeaders
{
    public static function nonce(): string
    {
        $request = request();
        if (! $request->attributes->has('csp_nonce')) {
            $request->attributes->set('csp_nonce', base64_encode(Str::random(24)));
        }

        return $request->attributes->get('csp_nonce');
    }

    public function handle(Request $request, Closure $next): Response
    {
        $nonce = self::nonce();
        $response = $next($request);

        if (function_exists('header_remove')) {
            @header_remove('X-Powered-By');
        }
        $response->headers->remove('X-Powered-By');

        $h = $response->headers;
        $h->set('X-Content-Type-Options', 'nosniff');
        $h->set('X-Frame-Options', 'DENY');
        $h->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $h->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=(), usb=(), interest-cohort=()');
        $h->set('Cross-Origin-Opener-Policy', 'same-origin-allow-popups');
        $h->set('X-Permitted-Cross-Domain-Policies', 'none');

        if ($request->isSecure()) {
            $h->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        // Admin, installer and checkout pages must never be indexed or cached by shared proxies
        if ($request->is('admin', 'admin/*', 'install', 'checkout', 'cart')) {
            $h->set('X-Robots-Tag', 'noindex, nofollow');
            if ($request->is('admin', 'admin/*', 'install')) {
                $h->set('Cache-Control', 'no-store, private');
            }
        }

        // Keep Laravel's detailed debug error page working while developing
        $isDebugError = config('app.debug') && $response->getStatusCode() >= 500;
        // Laravel's built-in /up health page loads its own CDN styling; it holds no customer data
        $isHealthPage = $request->is('up');
        if (! $isDebugError && ! $isHealthPage && ! $h->has('Content-Security-Policy')) {
            $h->set('Content-Security-Policy', implode('; ', [
                "default-src 'self'",
                "script-src 'self' 'nonce-{$nonce}'",
                "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com",
                "font-src 'self' https://fonts.gstatic.com data:",
                "img-src 'self' data: blob: https:",
                "connect-src 'self'",
                "manifest-src 'self'",
                "media-src 'self'",
                "object-src 'none'",
                "frame-src 'none'",
                "frame-ancestors 'none'",
                "base-uri 'self'",
                "form-action 'self'",
                'upgrade-insecure-requests',
            ]));
        }

        return $response;
    }
}
