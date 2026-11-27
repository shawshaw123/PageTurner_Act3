<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    // Artisan commands that belong to an integration live in its folder.
    ->withCommands([__DIR__.'/../app/Integrations'])
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \Illuminate\Session\Middleware\AuthenticateSession::class,
        ]);

        // IP-3: PayMongo cannot send a CSRF token; the webhook is protected by
        // its signature header instead (see PayMongo\WebhookSignature).
        $middleware->validateCsrfTokens(except: ['webhooks/paymongo']);

        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'seller' => \App\Http\Middleware\SellerMiddleware::class,
            'two-factor' => \App\Http\Middleware\TwoFactorMiddleware::class,
            'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
            'api.rate_limit' => \App\Http\Middleware\ApiRateLimitMiddleware::class,
            'api.transform' => \App\Http\Middleware\ApiDataTransformMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // The REST API always answers in JSON, even when the client does not send
        // "Accept: application/json". An unauthenticated request to a protected
        // endpoint therefore gets HTTP 401 instead of a redirect to the login page.
        $exceptions->shouldRenderJsonWhen(
            fn (\Illuminate\Http\Request $request) => $request->is('api/*') || $request->expectsJson()
        );
    })->create();
