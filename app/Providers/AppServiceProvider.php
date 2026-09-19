<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
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
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by($this->clientIp($request));
        });

        // Registration: 5 attempts/minute per IP — blocks account-creation spam.
        RateLimiter::for('register', function (Request $request) {
            return Limit::perMinute(5)->by($this->clientIp($request));
        });

        // OTP verify/resend: 5 attempts/minute per IP — the OTP is a 6-digit
        // code (900,000 possibilities), so unthrottled guessing would be feasible.
        RateLimiter::for('otp', function (Request $request) {
            return Limit::perMinute(5)->by($this->clientIp($request));
        });

        // Forgot/reset password: 5 attempts/minute per IP.
        RateLimiter::for('password-reset', function (Request $request) {
            return Limit::perMinute(5)->by($this->clientIp($request));
        });

        // Admin login: 5 attempts/minute per IP.
        RateLimiter::for('admin-login', function (Request $request) {
            return Limit::perMinute(5)->by($this->clientIp($request));
        });
    }

    /**
     * The visitor's real IP, used as the rate-limit key.
     *
     * Behind Railway's proxy the reliable source is the X-Real-IP header, which
     * Railway's edge sets to the client's remote address (per Railway's docs);
     * what Laravel derives from X-Forwarded-For can be a proxy hop that changes
     * between requests, which would give every request its own limit bucket.
     * Falls back to Laravel's own detection (local development, ngrok, tests).
     */
    private function clientIp(Request $request): string
    {
        $realIp = $request->headers->get('X-Real-IP');

        if (is_string($realIp) && filter_var($realIp, FILTER_VALIDATE_IP) !== false) {
            return $realIp;
        }

        return (string) $request->ip();
    }
}
