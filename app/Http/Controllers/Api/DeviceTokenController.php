<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreDeviceTokenRequest;
use App\Models\DeviceToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeviceTokenController extends Controller
{
    /**
     * Registers (or re-registers) this device's FCM token for the
     * authenticated user. Using the token itself as the unique key means a
     * device that logs in as a different user automatically "moves" to
     * that user instead of creating a duplicate row.
     */
    public function store(StoreDeviceTokenRequest $request): JsonResponse
    {
        $data = $request->validated();

        $values = [
            'user_id' => $request->user()->id,
            'platform' => $data['platform'] ?? null,
        ];

        // Only touched when the app actually sends it, so an older app build
        // that doesn't know about this field can't accidentally flip a
        // device's saved preference back on.
        if (array_key_exists('sound_enabled', $data)) {
            $values['sound_enabled'] = $data['sound_enabled'];
        }

        DeviceToken::query()->updateOrCreate(
            ['token' => $data['token']],
            $values,
        );

        return response()->json(['message' => __('messages.device_tokens.registered')], 201);
    }

    /**
     * Removes this device's token, e.g. on logout, so it stops receiving
     * push notifications for this user.
     */
    public function destroy(Request $request): JsonResponse
    {
        $request->validate([
            'token' => ['required', 'string'],
        ]);

        $request->user()
            ->deviceTokens()
            ->where('token', $request->string('token'))
            ->delete();

        return response()->json(['message' => __('messages.device_tokens.removed')]);
    }
}