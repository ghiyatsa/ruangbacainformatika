<?php

use App\Http\Middleware\EnsureKioskPinIsValid;
use App\Http\Middleware\EnsureProfileIsCompleted;
use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use App\Services\Auth\AuthenticationRedirector;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->encryptCookies(except: ['appearance', 'sidebar_state']);
        $middleware->redirectUsersTo(function (Request $request): string {
            $user = $request->user();

            if ($user === null) {
                return route('home', absolute: false);
            }

            return app(AuthenticationRedirector::class)->pathFor($user);
        });

        $middleware->web(append: [
            HandleAppearance::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);

        $middleware->alias([
            'profile.completed' => EnsureProfileIsCompleted::class,
            'kiosk.pin' => EnsureKioskPinIsValid::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
