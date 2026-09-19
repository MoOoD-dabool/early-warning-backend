<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\UserAlert;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class UserAlertSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (! Schema::hasTable('user_alerts')) {
            return;
        }

        try {
            UserAlert::factory()->count(10)->create();
        } catch (\Throwable $e) {
            $this->command?->warn("Skipped seeding user_alerts: " . $e->getMessage());
        }
    }
}
