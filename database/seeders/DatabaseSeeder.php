<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(CitySeeder::class);
        $this->call(ReliefTeamSeeder::class);
        $this->call(AdminSeeder::class);
        $this->call(DisasterTypeSeeder::class);
        $this->call(UserSeeder::class);
        $this->call(EarthquakeEventSeeder::class);
        $this->call(ReportSeeder::class);
        $this->call(AlertSeeder::class);
        $this->call(UserAlertSeeder::class);
    }
}
