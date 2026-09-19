<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\City;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class CitySeeder extends Seeder
{
    /**
     * IMPORTANT: the code/name order must stay identical to the hardcoded
     * `cities` list in the Flutter app (lib/main.dart). The Flutter app
     * sends the `code` (e.g. "SY001") as `city_code`, and the backend
     * resolves it to the matching city.
     *
     * latitude/longitude are the approximate governorate-capital
     * coordinates, used to find the nearest city to an earthquake epicenter.
     */
    private const GOVERNORATES = [
        ['name_ar' => 'دمشق', 'name_en' => 'Damascus', 'code' => 'SY001', 'lat' => 33.5138, 'lng' => 36.2765],
        ['name_ar' => 'ريف دمشق', 'name_en' => 'Rif Dimashq', 'code' => 'SY002', 'lat' => 33.5850, 'lng' => 36.4046],
        ['name_ar' => 'درعا', 'name_en' => 'Daraa', 'code' => 'SY003', 'lat' => 32.6189, 'lng' => 36.1021],
        ['name_ar' => 'القنيطرة', 'name_en' => 'Quneitra', 'code' => 'SY004', 'lat' => 33.1257, 'lng' => 35.8237],
        ['name_ar' => 'السويداء', 'name_en' => 'As-Suwayda', 'code' => 'SY005', 'lat' => 32.7094, 'lng' => 36.5697],
        ['name_ar' => 'حمص', 'name_en' => 'Homs', 'code' => 'SY006', 'lat' => 34.7304, 'lng' => 36.7137],
        ['name_ar' => 'تدمر', 'name_en' => 'Palmyra', 'code' => 'SY007', 'lat' => 34.5556, 'lng' => 38.2758],
        ['name_ar' => 'حماة', 'name_en' => 'Hama', 'code' => 'SY008', 'lat' => 35.1318, 'lng' => 36.7578],
        ['name_ar' => 'طرطوس', 'name_en' => 'Tartus', 'code' => 'SY009', 'lat' => 34.8890, 'lng' => 35.8866],
        ['name_ar' => 'اللاذقية', 'name_en' => 'Latakia', 'code' => 'SY010', 'lat' => 35.5317, 'lng' => 35.7915],
        ['name_ar' => 'إدلب', 'name_en' => 'Idlib', 'code' => 'SY011', 'lat' => 35.9306, 'lng' => 36.6339],
        ['name_ar' => 'حلب', 'name_en' => 'Aleppo', 'code' => 'SY012', 'lat' => 36.2021, 'lng' => 37.1343],
        ['name_ar' => 'الحسكة', 'name_en' => 'Al-Hasakah', 'code' => 'SY013', 'lat' => 36.5024, 'lng' => 40.7477],
        ['name_ar' => 'الرقة', 'name_en' => 'Raqqa', 'code' => 'SY014', 'lat' => 35.9528, 'lng' => 39.0079],
        ['name_ar' => 'دير الزور', 'name_en' => 'Deir ez-Zor', 'code' => 'SY015', 'lat' => 35.3359, 'lng' => 40.1408],
        ['name_ar' => 'القامشلي', 'name_en' => 'Qamishli', 'code' => 'SY016', 'lat' => 37.0522, 'lng' => 41.2364],
    ];

    public function run(): void
    {
        if (! Schema::hasTable('cities')) {
            return;
        }

        foreach (self::GOVERNORATES as $governorate) {
            City::query()->updateOrCreate(
                ['code' => $governorate['code']],
                [
                    'name_ar' => $governorate['name_ar'],
                    'name_en' => $governorate['name_en'],
                    'latitude' => $governorate['lat'],
                    'longitude' => $governorate['lng'],
                ],
            );
        }
    }
}