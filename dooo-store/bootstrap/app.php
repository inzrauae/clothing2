<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Not-logged-in visitors to /admin go to the admin login page
        $middleware->redirectGuestsTo(fn () => route('admin.login'));
        // Logged-in admins visiting the login page go to the dashboard
        $middleware->redirectUsersTo(fn () => route('admin.dashboard'));
        // Trust cPanel / Cloudflare proxies so HTTPS URLs are generated correctly
        $middleware->trustProxies(at: '*');
        // Security: optional http→https redirect, and security headers (CSP etc.) on every response
        $middleware->prepend(\App\Http\Middleware\ForceHttps::class);
        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
