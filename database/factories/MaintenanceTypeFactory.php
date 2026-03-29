<?php

namespace Database\Factories;

use App\Models\Motorcycle;
use Illuminate\Database\Eloquent\Factories\Factory;

class MaintenanceTypeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'motorcycle_id'        => Motorcycle::factory(),
            'name'                 => $this->faker->randomElement(['Vidange', 'Chaîne', 'Pneu avant', 'Filtre à air']),
            'interval_km'          => $this->faker->optional()->numberBetween(3000, 20000),
            'interval_days'        => $this->faker->optional()->numberBetween(180, 730),
            'alert_threshold_km'   => 500,
            'alert_threshold_days' => 30,
            'is_active'            => true,
        ];
    }
}
