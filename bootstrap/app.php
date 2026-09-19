<?php

use App\Http\Middleware\EnsureAdmin;
use App\Http\Middleware\ForceJsonUnicode;
use App\Http\Middleware\SetLocaleFromHeader;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // On a real host (e.g. Railway) every request arrives through the
        // host's HTTPS reverse proxy. Trusting its X-Forwarded-* headers lets
        // Laravel see the visitor's real IP (the login/OTP rate limits are
        // per IP — without this, all users would share ONE limit bucket) and
        // know the original request was https (correct admin-panel URLs).
        // Safe here because the container is only reachable through that proxy.
        $middleware->trustProxies(at: '*');

        $middleware->alias([
            'admin' => EnsureAdmin::class,
        ]);

        $middleware->api(append: [
            SetLocaleFromHeader::class,
            ForceJsonUnicode::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Every /api/* route is JSON in, JSON out, so an unauthenticated
        // request there should always get a clean 401 JSON response —
        // never Laravel's default "redirect to a login page" behavior,
        // which used to crash here since there was no such page.
        // The Filament admin panel (added later, under /admin) is a real
        // browser UI with its own login page, so it must keep Laravel's
        // default redirect-to-login behavior instead — returning null here
        // lets Laravel fall through to that default for any non-API route.
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return response()->json([
                'message' => __('messages.unauthenticated'),
            ], 401);
        });
    })->create();