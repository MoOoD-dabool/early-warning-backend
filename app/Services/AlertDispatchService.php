<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Alert;
use App\Models\DeviceToken;
use App\Models\User;
use App\Models\UserAlert;
use Carbon\Carbon;

class AlertDispatchService
{
    /**
     * How many device pushes failed to send during the most recent
     * dispatch() call (0 when everything went out). The in-app alert rows are
     * always saved regardless — this only lets a caller such as the admin
     * "Send Alert" page tell the operator that some real pushes didn't go out.
     */
    public int $lastPushFailures = 0;

    public function __construct(private readonly FcmService $fcmService)
    {
    }

    /**
     * Fan the alert out to every user living in the alert's city: creates a
     * user_alerts row for each of them (so the app shows it in the feed),
     * and sends a real push notification to every registered device of
     * those users through Firebase.
     */
    public function dispatch(Alert $alert): int
    {
        $this->lastPushFailures = 0;

        $userIds = User::query()
            ->where('city_id', $alert->city_id)
            ->pluck('id');

        $now = Carbon::now();

        $rows = $userIds->map(fn (int $userId) => [
            'user_id' => $userId,
            'alert_id' => $alert->id,
            'received_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ])->all();

        if (empty($rows)) {
            return 0;
        }

        UserAlert::query()->insert($rows);

        $this->sendPushNotifications($alert, $userIds->all());

        return count($rows);
    }

    private function sendPushNotifications(Alert $alert, array $userIds): void
    {
        $devices = DeviceToken::query()
            ->whereIn('user_id', $userIds)
            ->get(['token', 'sound_enabled']);

        if ($devices->isEmpty()) {
            return;
        }

        $alert->loadMissing('disasterType');

        $title = $alert->disasterType?->name_ar ?? 'تنبيه إنذار مبكر';

        $data = [
            'alert_id' => (string) $alert->id,
            'severity' => $alert->severity,
            'trigger_siren' => $alert->trigger_siren ? '1' : '0',
            'city_id' => (string) $alert->city_id,
        ];

        // Devices whose owner switched "alert sound" off in the app still get
        // the push, but through the phone's normal notification channel
        // (regular short tone) instead of the siren channel. Alerts that
        // don't call for a siren never use the siren channel for anyone.
        $sirenTokens = $alert->trigger_siren
            ? $devices->where('sound_enabled', true)->pluck('token')->all()
            : [];
        $normalTokens = $devices->pluck('token')->diff($sirenTokens)->values()->all();

        if (! empty($sirenTokens)) {
            $this->lastPushFailures += $this->fcmService->sendToTokens(
                tokens: $sirenTokens,
                title: $title,
                body: $alert->message_ar,
                data: $data,
                androidChannelId: config('firebase.siren_android_channel_id'),
            );
        }

        if (! empty($normalTokens)) {
            $this->lastPushFailures += $this->fcmService->sendToTokens(
                tokens: $normalTokens,
                title: $title,
                body: $alert->message_ar,
                data: $data,
                androidChannelId: null,
            );
        }
    }
}