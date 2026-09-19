<?php

declare(strict_types=1);

namespace App\Services;

use Firebase\JWT\JWT;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Sends real push notifications through Firebase Cloud Messaging using the
 * current HTTP v1 API (the old legacy "server key" API was shut down by
 * Google, this is the only supported way now).
 *
 * Auth flow: sign a short-lived JWT with the service account's private key,
 * exchange it for an OAuth2 access token, then use that token as a Bearer
 * header when calling FCM's send endpoint. The access token is cached
 * (valid ~1 hour) so we don't redo this handshake for every notification.
 */
class FcmService
{
    private const TOKEN_URL = 'https://oauth2.googleapis.com/token';

    private const SCOPE = 'https://www.googleapis.com/auth/firebase.messaging';

    /**
     * Sends the same notification to a list of device tokens. Failures for
     * individual tokens (e.g. an uninstalled app) are logged and skipped —
     * one bad token should never stop the rest from being delivered. A
     * network failure (timeout, dropped connection) is treated the same way:
     * retried once, then logged and skipped for that device only.
     *
     * @return int how many device tokens the push could NOT be delivered to
     */
    public function sendToTokens(
        array $tokens,
        string $title,
        string $body,
        array $data = [],
        ?string $androidChannelId = null,
    ): int {
        try {
            $accessToken = $this->getAccessToken();
        } catch (ConnectionException $e) {
            Log::error('FCM: network failure while obtaining an access token, skipping push send.', [
                'error' => $e->getMessage(),
            ]);

            return count($tokens);
        }

        if ($accessToken === null) {
            Log::error('FCM: could not obtain an access token, skipping push send.');

            return count($tokens);
        }

        $failed = 0;

        $projectId = config('firebase.project_id');
        $url = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";

        foreach ($tokens as $token) {
            $message = [
                'token' => $token,
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                ],
                'data' => array_map('strval', $data),
            ];

            // Routes the notification to the native Android channel that has
            // the custom siren sound configured, instead of the default
            // notification sound, when this alert calls for one.
            if ($androidChannelId !== null) {
                $message['android'] = [
                    'notification' => [
                        'channel_id' => $androidChannelId,
                        'sound' => 'alarm',
                    ],
                ];
            }

            try {
                // One quick retry (500ms apart) absorbs a momentary network
                // blip without noticeably delaying the rest of the batch. Only
                // connection failures are retried — a real rejection from FCM
                // (e.g. 404 for an uninstalled app's token) won't change on retry.
                $response = Http::withToken($accessToken)
                    ->retry(2, 500, fn ($exception) => $exception instanceof ConnectionException, throw: false)
                    ->post($url, ['message' => $message]);
            } catch (ConnectionException $e) {
                Log::warning('FCM: network failure while sending to a device token.', [
                    'error' => $e->getMessage(),
                ]);
                $failed++;

                continue;
            }

            if (! $response->successful()) {
                Log::warning('FCM: failed to send to a device token.', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                $failed++;
            }
        }

        return $failed;
    }

    private function getAccessToken(): ?string
    {
        return Cache::remember('fcm_access_token', 3000, function () {
            $credentials = $this->loadCredentials();

            if ($credentials === null) {
                return null;
            }

            $now = time();

            $jwt = JWT::encode([
                'iss' => $credentials['client_email'],
                'scope' => self::SCOPE,
                'aud' => self::TOKEN_URL,
                'iat' => $now,
                'exp' => $now + 3600,
            ], $credentials['private_key'], 'RS256');

            $response = Http::asForm()->post(self::TOKEN_URL, [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt,
            ]);

            if (! $response->successful()) {
                Log::error('FCM: failed to exchange JWT for an access token.', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return null;
            }

            return $response->json('access_token');
        });
    }

    /**
     * @return array{client_email: string, private_key: string}|null
     */
    private function loadCredentials(): ?array
    {
        $path = config('firebase.credentials_path');

        // Reads straight from storage/app (not via Storage::disk('local'),
        // whose configured root is storage/app/private as of this Laravel
        // version's default filesystems config) - this is where the real
        // service account file has actually always lived.
        $fullPath = storage_path("app/{$path}");

        if (! File::exists($fullPath)) {
            Log::error("FCM: service account file not found at {$fullPath}.");

            return null;
        }

        $json = json_decode(File::get($fullPath), true);

        if (! isset($json['client_email'], $json['private_key'])) {
            Log::error('FCM: service account file is missing client_email or private_key.');

            return null;
        }

        return [
            'client_email' => $json['client_email'],
            'private_key' => $json['private_key'],
        ];
    }
}