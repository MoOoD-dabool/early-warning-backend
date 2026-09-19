<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Reads the Accept-Language header the Flutter app sends with every
 * request (see ApiClient._headers() in lib/api_client.dart) and switches
 * Laravel's locale for this request so validation errors and app-specific
 * messages (lang/{locale}/validation.php, lang/{locale}/messages.php)
 * come back in the language the user is actually using — instead of
 * always defaulting to English regardless of the app's language setting.
 *
 * Only 'ar' and 'en' are supported (the only two languages the app has);
 * anything else, or a missing header, falls back to config('app.locale').
 */
class SetLocaleFromHeader
{
    private const SUPPORTED_LOCALES = ['ar', 'en'];

    public function handle(Request $request, Closure $next): Response
    {
        $locale = strtolower((string) $request->header('Accept-Language'));

        if (in_array($locale, self::SUPPORTED_LOCALES, true)) {
            app()->setLocale($locale);
        }

        return $next($request);
    }
}
