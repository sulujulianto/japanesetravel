<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\UserAddress>
 */
class UserAddressFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'label' => fake()->randomElement(['Home', 'Office']),
            'recipient_name' => fake()->name(),
            'recipient_phone' => '+62'.fake()->numerify('8##########'),
            'address_line_1' => fake()->streetAddress(),
            'address_line_2' => null,
            'city' => fake()->city(),
            'province' => fake()->state(),
            'postal_code' => fake()->postcode(),
            'country_code' => 'ID',
            'is_default' => false,
        ];
    }

    public function asDefault(): static
    {
        return $this->state(fn (): array => ['is_default' => true]);
    }
}
