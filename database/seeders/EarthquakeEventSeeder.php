<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\EarthquakeEvent;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class EarthquakeEventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (! Schema::hasTable('earthquake_events')) {
            return;
        }

        try {
            EarthquakeEvent::factory()->count(10)->create();
        } catch (\Throwable $e) {
            $this->command?->warn("Skipped seeding earthquake_events: " . $e->getMessage());
        }
    }
}
