<?php

namespace Database\Factories;

use App\Models\Motorcycle;
use Illuminate\Database\Eloquent\Factories\Factory;

class MaintenanceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'motorcycle_id'        => Motorcycle::factory(),
            'mileage'              => $this->faker->numberBetween(1000, 50000),
            'performed_at'         => $this->faker->dateTimeBetween('-3 years', 'now')->format('Y-m-d'),
            'garage'               => $this->faker->optional()->company(),
            'total_amount'         => $this->faker->optional()->randomFloat(2, 50, 2000),
            'notes'                => null,
            'ai_extraction_status' => 'done',
        ];
    }
}
