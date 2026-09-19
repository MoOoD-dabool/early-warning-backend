<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\City;
use App\Services\EmscEarthquakeProcessor;
use Illuminate\Console\Command;

/**
 * TESTING ONLY. Builds a fake EMSC message and feeds it straight into
 * EmscEarthquakeProcessor, exactly like a real WebSocket message would —
 * so you can verify the whole pipeline (filtering, nearest-city matching,
 * alert creation, dispatch) without waiting for a real earthquake or
 * touching the network at all.
 *
 * Usage examples:
 *   php artisan earthquake:simulate 4.5 SY012
 *   php artisan earthquake:simulate 1.5 SY001   (below threshold, should be dropped)
 *   php artisan earthquake:simulate 2.5 SY006   (text-only alert, no siren)
 */
class SimulateEarthquake extends Command
{
    protected $signature = 'earthquake:simulate {magnitude=4.5} {city_code=SY001}';

    protected $description = 'TESTING ONLY: simulate a fake EMSC earthquake message for the given magnitude and city code.';

    public function handle(EmscEarthquakeProcessor $processor): int
    {
        $magnitude = (float) $this->argument('magnitude');
        $cityCode = (string) $this->argument('city_code');

        $city = City::query()->where('code', $cityCode)->first();

        if (! $city) {
            $this->error("No city found with code '{$cityCode}'. Check GET /api/v1/cities for valid codes.");

            return self::FAILURE;
        }

        $properties = [
            'unid' => 'TEST_'.now()->format('YmdHis'),
            'mag' => $magnitude,
            'lat' => (float) $city->latitude,
            'lon' => (float) $city->longitude,
            'depth' => 10.0,
            'time' => now()->toIso8601String(),
            'flynn_region' => 'SIMULATED TEST EVENT - '.$city->name_en,
        ];

        $this->info("Simulating a magnitude {$magnitude} earthquake at {$city->name_en} ({$cityCode})...");

        $processor->handle($properties);

        $this->info('Done. Check GET /api/v1/admin/alerts or /api/v1/my-alerts to see the result.');
        $this->comment('(If magnitude was below the ignore_below threshold, nothing was created — that is expected.)');

        return self::SUCCESS;
    }
}