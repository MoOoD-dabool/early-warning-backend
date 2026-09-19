<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\User;
use App\Models\City;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'city_id' => fn () => City::query()->inRandomOrder()->value('id') ?? City::factory(),
            'street_name' => fake()->text(50),
            'building_number' => fake()->text(50),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'profile_image' => fake()->boolean(70) ? fake()->text(50) : null,
            'remember_token' => Str::random(10),
            'is_verified' => fake()->boolean(),
            'otp_code' => fake()->boolean(70) ? fake()->text(50) : null,
            'otp_expires_at' => fake()->boolean(70) ? fake()->dateTime() : null,
        ];
    }
    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
