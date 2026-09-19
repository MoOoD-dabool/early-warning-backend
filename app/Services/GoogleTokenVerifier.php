<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Verifies a Google ID token the Flutter app obtained via google_sign_in,
 * using Google's own tokeninfo endpoint — simple and reliable at this
 * app's scale, no local JWKS/signature verification needed.
 * https://developers.google.com/identity/sign-in/web/backend-auth
 */
class GoogleTokenVerifier
{
    private const TOKENINFO_URL = 'https://oauth2.googleapis.com/tokeninfo';

    /**
     * Returns the verified token payload only if the token is genuinely
     * valid, was issued for this app's own OAuth client (never a token
     * meant for some other app), and its email is actually verified.
     * Returns null on any failure.
     *
     * @return array{sub: string, email: string, given_name: string, family_name: string}|null
     */
    public function verify(string $idToken): ?array
    {
        try {
            $response = Http::timeout(10)->get(self::TOKENINFO_URL, [
                'id_token' => $idToken,
            ]);

            if (! $response->successful()) {
                return null;
            }

            $payload = $response->json();

            if (! is_array($payload) || ! isset($payload['sub'], $payload['email'])) {
                return null;
            }

            $expectedAudience = config('services.google.client_id');

            if (! $expectedAudience || ($payload['aud'] ?? null) !== $expectedAudience) {
                Log::warning('Google auth: token audience mismatch.', ['aud' => $payload['aud'] ?? null]);

                return null;
            }

            if (! filter_var($payload['email_verified'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
                return null;
            }

            return [
                'sub' => (string) $payload['sub'],
                'email' => (string) $payload['email'],
                'given_name' => (string) ($payload['given_name'] ?? ''),
                'family_name' => (string) ($payload['family_name'] ?? ''),
            ];
        } catch (\Throwable $e) {
            Log::warning('Google auth: token verification request failed.', ['error' => $e->getMessage()]);

            return null;
        }
    }
}
