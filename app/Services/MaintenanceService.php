<?php

namespace App\Services;

use App\Models\Maintenance;
use App\Models\Motorcycle;
use Illuminate\Support\Facades\DB;

class MaintenanceService
{
    public function create(Motorcycle $motorcycle, array $data): Maintenance
    {
        return DB::transaction(function () use ($motorcycle, $data) {
            $maintenance = $motorcycle->maintenances()->create([
                'performed_at' => $data['performed_at'],
                'mileage'      => $data['mileage'],
                'garage'       => $data['garage'] ?? null,
                'notes'        => $data['notes'] ?? null,
            ]);

            foreach ($data['items'] as $item) {
                $maintenance->maintenanceItems()->create([
                    'label'  => $item['label'],
                    'amount' => $item['amount'] ?? null,
                ]);
            }

            return $maintenance;
        });
    }
}
