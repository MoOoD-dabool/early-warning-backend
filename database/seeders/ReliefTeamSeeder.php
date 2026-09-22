<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\City;
use App\Models\DisasterType;
use App\Models\ReliefTeam;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class ReliefTeamSeeder extends Seeder
{
    private const TEAMS = [
        [
            'city_code' => 'SY012', // Aleppo
            'street' => 'شارع النيل',
            'building_number' => '42',
            'status' => 'active',
            'relief_type' => 'search_rescue',
            'disaster_type_key' => 'earthquake',
            'deployed_hours_ago' => 6,
        ],
        [
            'city_code' => 'SY001', // Damascus
            'street' => 'شارع الثورة',
            'building_number' => '17',
            'status' => 'active',
            'relief_type' => 'medical',
            'disaster_type_key' => 'earthquake',
            'deployed_hours_ago' => 30,
        ],
        [
            'city_code' => 'SY006', // Homs — a Rastan-tier flood city
            'street' => 'شارع باب السباع',
            'building_number' => '8',
            'status' => 'active',
            'relief_type' => 'logistics',
            'disaster_type_key' => 'flood',
            'deployed_hours_ago' => 20,
        ],
        [
            'city_code' => 'SY010', // Latakia — coastal
            'street' => 'شارع الجمهورية',
            'building_number' => '23',
            'status' => 'active',
            'relief_type' => 'food_water',
            'disaster_type_key' => 'coastal_storm',
            'deployed_hours_ago' => 48,
        ],
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

            $disasterTypeId = DisasterType::query()->where('key', $team['disaster_type_key'])->value('id');

            ReliefTeam::query()->updateOrCreate(
                [
                    'city_id' => $cityId,
                    'street' => $team['street'],
                    'building_number' => $team['building_number'],
                ],
                [
                    'status' => $team['status'],
                    'relief_type' => $team['relief_type'],
                    'disaster_type_id' => $disasterTypeId,
                    'deployed_at' => now()->subHours($team['deployed_hours_ago']),
                ],
            );
        }
    }
}
