<?php

namespace Database\Factories;

use App\Models\Souvenir;
use Illuminate\Database\Eloquent\Factories\Factory;

class SouvenirFactory extends Factory
{
    protected $model = Souvenir::class;

    public function definition(): array
    {
        $nameId = 'Souvenir ' . $this->faker->unique()->word();
        $nameEn = 'Souvenir ' . $this->faker->unique()->word();

        return [
            'name' => [
                'id' => $nameId,
                'en' => $nameEn,
            ],
            'description' => [
                'id' => $this->faker->sentence(8),
                'en' => $this->faker->sentence(8),
            ],
            'price' => $this->faker->numberBetween(40000, 200000),
            'stock' => $this->faker->numberBetween(5, 80),
            'image' => null,
        ];
    }
}
