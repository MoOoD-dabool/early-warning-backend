<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\City;
use App\Models\WeatherReading;
use App\Services\WeatherAlertService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * TESTING ONLY. Inserts a fake WeatherReading with a dangerous value and
 * runs it straight through WeatherAlertService, so you can verify the
 * heavy-rain and strong-wind alert logic without waiting for real severe
 * weather.
 *
 * Usage examples:
 *   php artisan weather:simulate-alert SY012 rain       (thunderstorm test)
 *   php artisan weather:simulate-alert SY006 wind        (storm test)
 */
class SimulateWeatherAlert extends Command
{
    protected $signature = 'weather:simulate-alert {city_code=SY001} {scenario=rain}';

    protected $description = 'TESTING ONLY: simulate a dangerous weather reading (rain or wind) for a city.';

    public function handle(WeatherAlertService $weatherAlertService): int
    {
        $cityCode = (string) $this->argument('city_code');
        $scenario = (string) $this->argument('scenario');

        $city = City::query()->where('code', $cityCode)->first();

        if (! $city) {
            $this->error("No city found with code '{$cityCode}'.");

            return self::FAILURE;
        }

        $data = match ($scenario) {
            'rain' => [
                'temperature_c' => 18.0,
                'humidity_percent' => 90,
                'wind_speed_kmh' => 20.0,
                'pressure_msl' => 1008.0,
                'weather_code' => 95, // thunderstorm
            ],
            'wind' => [
                'temperature_c' => 22.0,
                'humidity_percent' => 60,
                'wind_speed_kmh' => 90.0, // clears both inland (60/80) and coastal (65/90) critical thresholds
                'pressure_msl' => 990.0, // clears both inland (1000) and coastal (995) thresholds
                'precipitation_mm' => 5.0, // clears the shared 1.0mm/h storm rain gate
                'weather_code' => 65,
            ],
            default => null,
        };

        if ($data === null) {
            $this->error("Unknown scenario '{$scenario}'. Use 'rain' or 'wind'.");

            return self::FAILURE;
        }

        $reading = WeatherReading::query()->create([
            'city_id' => $city->id,
            ...$data,
            'fetched_at' => Carbon::now(),
        ]);
        $reading->setRelation('city', $city);

        $this->info("Simulating '{$scenario}' scenario at {$city->name_en} ({$cityCode})...");

        $weatherAlertService->evaluate($reading);

        $this->info('Done. Check GET /api/v1/admin/alerts to see the result.');

        return self::SUCCESS;
    }
}