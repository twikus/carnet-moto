<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class MotorcycleFactory extends Factory
{
    public function definition(): array
    {
        return [
            'make'            => $this->faker->randomElement(['Honda', 'Yamaha', 'Kawasaki', 'Suzuki', 'BMW']),
            'model'           => $this->faker->bothify('??###'),
            'year'            => $this->faker->numberBetween(2000, 2025),
            'plate'           => $this->faker->optional()->bothify('??-###-??'),
            'initial_mileage' => $this->faker->numberBetween(0, 50000),
            'photo_path'      => null,
        ];
    }
}
