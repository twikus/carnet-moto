<?php

namespace App\Services;

use App\Models\Maintenance;
use App\Models\Motorcycle;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class MaintenanceService
{
    public function findForMotorcycle(Motorcycle $motorcycle, Maintenance $maintenance): Maintenance
    {
        return $motorcycle->maintenances()
            ->with('maintenanceItems.maintenanceType', 'invoices')
            ->findOrFail($maintenance->id);
    }

    public function listForMotorcycle(Motorcycle $motorcycle): Collection
    {
        return $motorcycle->maintenances()
            ->with('maintenanceItems.maintenanceType')
            ->orderBy('mileage', 'asc')
            ->orderBy('performed_at', 'asc')
            ->get();
    }

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
