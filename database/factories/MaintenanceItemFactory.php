<?php

namespace Database\Factories;

use App\Models\Maintenance;
use Illuminate\Database\Eloquent\Factories\Factory;

class MaintenanceItemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'maintenance_id'      => Maintenance::factory(),
            'maintenance_type_id' => null,
            'label'               => $this->faker->randomElement(['Vidange huile', 'Filtre à huile', 'Pneu avant', 'Chaîne']),
            'amount'              => $this->faker->optional()->randomFloat(2, 10, 500),
            'notes'               => null,
        ];
    }
}
