<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\City;
use App\Models\EarthquakeEvent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EarthquakeEvent>
 */
class EarthquakeEventFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = EarthquakeEvent::class;

    /**
     * Realistic-looking test data: magnitude 2.0–8.5 (Richter), depth
     * 2–700km, and location names referencing Syria's Dead Sea Transform
     * fault zone (the region's main seismic source) instead of random text.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'event_id' => fake()->unique()->bothify('EQ-####-????'),
            'magnitude' => fake()->randomFloat(1, 2.0, 8.5),
            'depth_km' => fake()->randomFloat(1, 2, 700),
            'location_name' => fake()->randomElement([
                'قرب فالق البحر الميت، جنوب دمشق',
                'شمال غرب حلب',
                'الساحل السوري قرب اللاذقية',
                'غرب حمص',
                'جنوب شرق حماة',
                'قرب الحدود اللبنانية السورية',
                'شمال طرطوس',
                'شرق درعا',
            ]),
            'city_id' => fn () => City::query()->inRandomOrder()->value('id') ?? City::factory(),
            'processed' => fake()->boolean(30),
            'occurred_at' => fake()->dateTimeBetween('-1 year', 'now'),
        ];
    }
}