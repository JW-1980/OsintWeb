<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            // Load installation routes
            Route::middleware('web')
                ->group(base_path('routes/install.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->api(prepend: [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        ]);

        // Add installation check to all web routes
        $middleware->web(append: [
            \App\Http\Middleware\CheckInstallation::class,
        ]);

        $middleware->alias([
            'verified' => \App\Http\Middleware\EnsureEmailIsVerified::class,
            'install.check' => \App\Http\Middleware\RedirectIfInstalled::class,
            'log.admin' => \App\Http\Middleware\LogAdminActions::class,
            'track.analytics' => \App\Http\Middleware\TrackAnalytics::class,
            'captcha' => \App\Http\Middleware\ValidateCaptcha::class,
        ]);

        // Add analytics tracking to web routes (non-API)
        $middleware->web(append: [
            \App\Http\Middleware\TrackAnalytics::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
