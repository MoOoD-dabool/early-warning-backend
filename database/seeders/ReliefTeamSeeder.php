<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\City;
use App\Models\ReliefTeam;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class ReliefTeamSeeder extends Seeder
{
    private const TEAMS = [
        ['city_code' => 'SY012', 'street' => 'شارع النيل', 'building_number' => '42'],
        ['city_code' => 'SY001', 'street' => 'شارع الثورة', 'building_number' => '17'],
        ['city_code' => 'SY006', 'street' => 'شارع باب السباع', 'building_number' => '8'],
        ['city_code' => 'SY010', 'street' => 'شارع الجمهورية', 'building_number' => '23'],
    ];

    public function run(): void
    {
        if (! Schema::hasTable('relief_teams')) {
            return;
        }

        foreach (self::TEAMS as $team) {
            $cityId = City::query()->where('code', $team['city_code'])->value('id');

            if (! $cityId) {
                continue;
            }

            ReliefTeam::query()->updateOrCreate(
                [
                    'city_id' => $cityId,
                    'street' => $team['street'],
                    'building_number' => $team['building_number'],
                ],
                ['is_active' => true],
            );
        }
    }
}
