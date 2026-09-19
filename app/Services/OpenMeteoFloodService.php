<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Real river discharge (m³/s) from Open-Meteo's Flood API — the same
 * provider as the weather API, free and keyless for non-commercial use,
 * built on the global GloFAS model. Used to tell whether a river near a
 * dam/floodplain city is running abnormally high right now compared to
 * its own recent history.
 *
 * Known limitation: at ~5km resolution, the model may not resolve small
 * local rivers precisely and instead reflects the nearest sizeable
 * waterway — reliability is best for major rivers (e.g. the Euphrates)
 * and weaker for smaller ones (e.g. the coastal rivers near Tartus/
 * Latakia). This is a limitation of the underlying model, not something
 * this project can correct.
 */
class OpenMeteoFloodService
{
    private const BASE_URL = 'https://flood-api.open-meteo.com/v1/flood';

    /**
     * Daily river discharge for the last `$pastDays` days up to today,
     * oldest first.
     *
     * @return array{time: array<string>, discharge: array<float>}|null
     */
    public function fetchDischarge(float $latitude, float $longitude, int $pastDays): ?array
    {
        try {
            $response = Http::timeout(10)->get(self::BASE_URL, [
                'latitude' => $latitude,
                'longitude' => $longitude,
                'daily' => 'river_discharge',
                'past_days' => $pastDays,
                'forecast_days' => 0,
            ]);

            if (! $response->successful()) {
                Log::warning('OpenMeteoFlood: request failed.', ['status' => $response->status()]);

                return null;
            }

            $daily = $response->json('daily');

            if (! is_array($daily) || ! isset($daily['river_discharge'], $daily['time'])) {
                Log::warning('OpenMeteoFlood: unexpected response shape.', ['body' => $response->body()]);

                return null;
            }

            return [
                'time' => $daily['time'],
                'discharge' => array_map('floatval', $daily['river_discharge']),
            ];
        } catch (\Throwable $e) {
            Log::error('OpenMeteoFlood: exception while fetching river discharge.', ['message' => $e->getMessage()]);

            return null;
        }
    }
}
