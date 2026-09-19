<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Every generated URL (admin panel links, assets, redirects) uses
        // https on a real server. Local development stays on plain http.
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        // Login: 5 attempts/minute per IP — blocks password brute-forcing.
        RateLimiter::for('login', function ($request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        // Registration: 5 attempts/minute per IP — blocks account-creation spam.
        RateLimiter::for('register', function ($request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        // OTP verify/resend: 5 attempts/minute per IP — the OTP is a 6-digit
        // code (900,000 possibilities), so unthrottled guessing would be feasible.
        RateLimiter::for('otp', function ($request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        // Forgot/reset password: 5 attempts/minute per IP.
        RateLimiter::for('password-reset', function ($request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        // Admin login: 5 attempts/minute per IP.
        RateLimiter::for('admin-login', function ($request) {
            return Limit::perMinute(5)->by($request->ip());
        });
    }
}
