<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Alert;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class AlertSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (! Schema::hasTable('alerts')) {
            return;
        }

        try {
            Alert::factory()->count(10)->create();
        } catch (\Throwable $e) {
            $this->command?->warn("Skipped seeding alerts: " . $e->getMessage());
        }
    }
}
