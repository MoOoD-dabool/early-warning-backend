<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\DisasterType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DisasterType>
 */
class DisasterTypeFactory extends Factory
{
    protected $model = DisasterType::class;

    public function definition(): array
    {
        return [
            'key' => fake()->unique()->slug(2),
            'name_ar' => fake()->unique()->randomElement(['زلازل', 'سيول', 'فيضانات', 'أعاصير', 'تسونامي']),
            'name_en' => fake()->unique()->randomElement(['Earthquakes', 'Flash Floods', 'Floods', 'Hurricanes', 'Tsunami']),
            'instructions_before_ar' => fake()->paragraph(),
            'instructions_before_en' => fake()->paragraph(),
            'instructions_during_ar' => fake()->paragraph(),
            'instructions_during_en' => fake()->paragraph(),
            'instructions_after_ar' => fake()->paragraph(),
            'instructions_after_en' => fake()->paragraph(),
            'audio_file_ar' => null,
            'audio_file_en' => null,
        ];
    }
}