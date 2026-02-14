<?php

use App\Http\Middleware\AuthenticateWorkOS;
use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\SkipNgrokBrowserWarning;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {

        // ⭐ IMPORTANT: Trust reverse proxy headers (fix HTTPS detection behind Caddy)
        $middleware->trustProxies(
            at: '*',
            headers: SymfonyRequest::HEADER_X_FORWARDED_FOR
                | SymfonyRequest::HEADER_X_FORWARDED_HOST
                | SymfonyRequest::HEADER_X_FORWARDED_PORT
                | SymfonyRequest::HEADER_X_FORWARDED_PROTO
                | SymfonyRequest::HEADER_X_FORWARDED_PREFIX
        );

        // 🍪 Cookies
        $middleware->encryptCookies(except: [
            'appearance',
            'sidebar_state',
        ]);

        // 🛡️ CSRF EXCEPTION (Laravel 12 way)
        $middleware->validateCsrfTokens(except: [
            'api/v1/token',
            'auth/workos/callback',
            'stripe/webhook',
        ]);

        // 🔐 API Auth
        $middleware->alias([
            'auth.workos' => AuthenticateWorkOS::class,
        ]);

        $middleware->api(append: [
            \App\Http\Middleware\AddRequestId::class,
        ]);

        // 🌐 Web / Inertia stack
        $middleware->web(prepend: [
            SkipNgrokBrowserWarning::class,
        ]);

        $middleware->web(append: [
            HandleAppearance::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->create();



