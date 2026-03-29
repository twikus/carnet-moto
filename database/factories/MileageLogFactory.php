<?php

namespace Database\Factories;

use App\Models\Motorcycle;
use Illuminate\Database\Eloquent\Factories\Factory;

class MileageLogFactory extends Factory
{
    public function definition(): array
    {
        return [
            'motorcycle_id' => Motorcycle::factory(),
            'mileage'       => $this->faker->numberBetween(1000, 50000),
            'logged_at'     => $this->faker->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
        ];
    }
}
