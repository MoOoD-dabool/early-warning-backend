<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Report;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class ReportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (! Schema::hasTable('reports')) {
            return;
        }

        try {
            Report::factory()->count(10)->create();
        } catch (\Throwable $e) {
            $this->command?->warn("Skipped seeding reports: " . $e->getMessage());
        }
    }
}
