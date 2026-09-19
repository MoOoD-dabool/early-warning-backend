<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Only the reference data the app cannot work without: the 16 governorates,
 * the disaster types (with their precaution texts) and the relief teams.
 *
 * Use this on a real server instead of DatabaseSeeder, which also inserts
 * demo users, earthquakes, reports and alerts meant for local testing.
 * Every seeder called here uses updateOrCreate, so running it again is safe.
 *
 *     php artisan db:seed --class=ProductionSeeder --force
 */
class ProductionSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CitySeeder::class,
            DisasterTypeSeeder::class,
            ReliefTeamSeeder::class,
        ]);
    }
}
