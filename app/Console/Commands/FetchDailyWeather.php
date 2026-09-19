<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\City;
use App\Models\WeatherReading;
use App\Services\OpenMeteoWeatherService;
use App\Services\WeatherAlertService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Fetches the current weather (temperature, humidity, wind speed, pressure)
 * for all 16 Syrian governorates from Open-Meteo, stores one reading per
 * city, and immediately checks each reading against the severe-weather
 * thresholds (heavy rain/thunderstorm, strong wind + low pressure) — any
 * city that crosses one gets a real disaster alert dispatched right away.
 * Scheduled to run automatically every 8 hours (see routes/console.php).
 */
class FetchDailyWeather extends Command
{
    protected $signature = 'weather:fetch';

    protected $description = 'Fetch current weather for every Syrian governorate and check for severe-weather alerts.';

    public function handle(OpenMeteoWeatherService $weatherService, WeatherAlertService $weatherAlertService): int
    {
        $cities = City::all();
        $now = Carbon::now();
        $success = 0;
        $failed = 0;

        foreach ($cities as $city) {
            $data = $weatherService->fetchCurrent((float) $city->latitude, (float) $city->longitude);

            if ($data === null) {
                $failed++;
                $this->warn("Failed to fetch weather for {$city->name_en} ({$city->code}).");

                continue;
            }

            $reading = WeatherReading::query()->create([
                'city_id' => $city->id,
                'temperature_c' => $data['temperature_c'],
                'humidity_percent' => $data['humidity_percent'],
                'wind_speed_kmh' => $data['wind_speed_kmh'],
                'pressure_msl' => $data['pressure_msl'],
                'precipitation_mm' => $data['precipitation_mm'],
                'weather_code' => $data['weather_code'],
                'fetched_at' => $now,
            ]);

            $reading->setRelation('city', $city);
            $weatherAlertService->evaluate($reading);

            $success++;
        }

        $this->info("Weather fetch complete: {$success} succeeded, {$failed} failed.");

        return self::SUCCESS;
    }
}