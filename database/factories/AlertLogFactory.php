<?php

namespace Database\Factories;

use App\Models\AlertLog;
use App\Models\MaintenanceType;
use App\Models\Motorcycle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AlertLog>
 */
class AlertLogFactory extends Factory
{
    protected $model = AlertLog::class;

    public function definition(): array
    {
        return [
            'motorcycle_id'       => Motorcycle::factory(),
            'maintenance_type_id' => MaintenanceType::factory(),
            'channel'             => 'discord',
            'message'             => $this->faker->sentence(),
            'sent_at'             => now(),
        ];
    }
}
