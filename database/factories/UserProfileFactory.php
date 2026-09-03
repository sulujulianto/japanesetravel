<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\UserProfile>
 */
class UserProfileFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'full_name' => fake()->name(),
            'phone' => '+62'.fake()->numerify('8##########'),
            'preferred_locale' => fake()->randomElement(['id', 'en']),
        ];
    }
}
