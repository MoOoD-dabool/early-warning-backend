<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Alert;
use App\Models\DisasterType;
use App\Models\City;
use App\Models\EarthquakeEvent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Alert>
 */
class AlertFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = Alert::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'disaster_type_id' => fn () => DisasterType::query()->inRandomOrder()->value('id') ?? DisasterType::factory(),
            'city_id' => fn () => City::query()->inRandomOrder()->value('id') ?? City::factory(),
            'earthquake_event_id' => fn () => EarthquakeEvent::query()->inRandomOrder()->value('id') ?? EarthquakeEvent::factory(),
            'severity' => fake()->randomElement(['low', 'medium', 'high', 'critical']),
            'message_ar' => fake('ar_SA')->paragraph(),
            'message_en' => fake()->paragraph(),
            'issued_at' => fake()->dateTime(),
        ];
    }
}