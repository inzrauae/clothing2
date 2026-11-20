<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * When FORCE_HTTPS=true (set it after SSL is active on cPanel), every plain-http
 * request is permanently redirected to https.
 */
class ForceHttps
{
    public function handle(Request $request, Closure $next): Response
    {
        if (config('security.force_https') && ! $request->isSecure() && ! app()->runningUnitTests()) {
            return redirect()->secure($request->getRequestUri(), 301);
        }

        return $next($request);
    }
}
