<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Fetches real current weather (temperature, humidity, wind speed) from
 * Open-Meteo — a free weather API that needs no API key. Docs:
 * https://open-meteo.com/en/docs
 */
class OpenMeteoWeatherService
{
    private const BASE_URL = 'https://api.open-meteo.com/v1/forecast';

    /**
     * Returns null on any failure (network error, bad response) so the
     * caller can skip that city and keep going instead of crashing the
     * whole daily fetch over one bad request.
     *
     * @return array{temperature_c: float, humidity_percent: int, wind_speed_kmh: float, weather_code: int|null, pressure_msl: float|null, precipitation_mm: float|null}|null
     */
    public function fetchCurrent(float $latitude, float $longitude): ?array
    {
        try {
            $response = Http::timeout(10)->get(self::BASE_URL, [
                'latitude' => $latitude,
                'longitude' => $longitude,
                'current' => 'temperature_2m,relative_humidity_2m,wind_speed_10m,weather_code,pressure_msl,precipitation',
                'timezone' => 'auto',
            ]);

            if (! $response->successful()) {
                Log::warning('OpenMeteo: request failed.', ['status' => $response->status()]);

                return null;
            }

            $current = $response->json('current');

            if (! is_array($current) || ! isset($current['temperature_2m'])) {
                Log::warning('OpenMeteo: unexpected response shape.', ['body' => $response->body()]);

                return null;
            }

            return [
                'temperature_c' => (float) $current['temperature_2m'],
                'humidity_percent' => (int) ($current['relative_humidity_2m'] ?? 0),
                'wind_speed_kmh' => (float) ($current['wind_speed_10m'] ?? 0),
                'weather_code' => isset($current['weather_code']) ? (int) $current['weather_code'] : null,
                'pressure_msl' => isset($current['pressure_msl']) ? (float) $current['pressure_msl'] : null,
                'precipitation_mm' => isset($current['precipitation']) ? (float) $current['precipitation'] : null,
            ];
        } catch (\Throwable $e) {
            Log::error('OpenMeteo: exception while fetching weather.', ['message' => $e->getMessage()]);

            return null;
        }
    }

    /**
     * Hour-by-hour precipitation for the last `$pastDays` days, used to
     * compute real rain accumulation (e.g. "how many mm fell in the last 8
     * hours") — the `current` endpoint above only gives a single instant
     * sample, not enough to answer that on its own.
     *
     * @return array{time: array<string>, precipitation_mm: array<float>}|null
     */
    public function fetchHourlyPrecipitation(float $latitude, float $longitude, int $pastDays): ?array
    {
        try {
            $response = Http::timeout(10)->get(self::BASE_URL, [
                'latitude' => $latitude,
                'longitude' => $longitude,
                'hourly' => 'precipitation',
                'past_days' => $pastDays,
                'forecast_days' => 0,
                'timezone' => 'auto',
            ]);

            if (! $response->successful()) {
                Log::warning('OpenMeteo: hourly precipitation request failed.', ['status' => $response->status()]);

                return null;
            }

            $hourly = $response->json('hourly');

            if (! is_array($hourly) || ! isset($hourly['precipitation'], $hourly['time'])) {
                Log::warning('OpenMeteo: unexpected hourly response shape.', ['body' => $response->body()]);

                return null;
            }

            return [
                'time' => $hourly['time'],
                'precipitation_mm' => array_map('floatval', $hourly['precipitation']),
            ];
        } catch (\Throwable $e) {
            Log::error('OpenMeteo: exception while fetching hourly precipitation.', ['message' => $e->getMessage()]);

            return null;
        }
    }

    /**
     * Day-by-day forecast (today + the next `$days - 1` days), used by the
     * app's weather-detail screen. `timezone=auto` makes Open-Meteo resolve
     * the city's own local timezone from its coordinates (Asia/Damascus for
     * every Syrian governorate), so "today" lines up with the user's day
     * instead of UTC's.
     *
     * @return array<int, array{date: string, temperature_max_c: float, temperature_min_c: float, humidity_percent: int, wind_speed_kmh: float, weather_code: int|null}>|null
     */
    public function fetchDailyForecast(float $latitude, float $longitude, int $days = 7): ?array
    {
        try {
            $response = Http::timeout(10)->get(self::BASE_URL, [
                'latitude' => $latitude,
                'longitude' => $longitude,
                'daily' => 'weather_code,temperature_2m_max,temperature_2m_min,wind_speed_10m_max,relative_humidity_2m_mean',
                'forecast_days' => $days,
                'timezone' => 'auto',
            ]);

            if (! $response->successful()) {
                Log::warning('OpenMeteo: forecast request failed.', ['status' => $response->status()]);

                return null;
            }

            $daily = $response->json('daily');

            if (! is_array($daily) || ! isset($daily['time'])) {
                Log::warning('OpenMeteo: unexpected forecast response shape.', ['body' => $response->body()]);

                return null;
            }

            $result = [];

            foreach ($daily['time'] as $i => $date) {
                $result[] = [
                    'date' => $date,
                    'temperature_max_c' => (float) ($daily['temperature_2m_max'][$i] ?? 0),
                    'temperature_min_c' => (float) ($daily['temperature_2m_min'][$i] ?? 0),
                    'humidity_percent' => (int) round($daily['relative_humidity_2m_mean'][$i] ?? 0),
                    'wind_speed_kmh' => (float) ($daily['wind_speed_10m_max'][$i] ?? 0),
                    'weather_code' => isset($daily['weather_code'][$i]) ? (int) $daily['weather_code'][$i] : null,
                ];
            }

            return $result;
        } catch (\Throwable $e) {
            Log::error('OpenMeteo: exception while fetching forecast.', ['message' => $e->getMessage()]);

            return null;
        }
    }
}