<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\UserAlert;
use App\Models\User;
use App\Models\Alert;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserAlert>
 */
class UserAlertFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = UserAlert::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'alert_id' => Alert::factory(),
            'received_at' => fake()->dateTime(),
        ];
    }
}
