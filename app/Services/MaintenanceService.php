<?php

namespace App\Services;

use App\Models\Maintenance;
use App\Models\Motorcycle;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

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

    public function update(Maintenance $maintenance, array $data): Maintenance
    {
        return DB::transaction(function () use ($maintenance, $data) {
            $maintenance->update([
                'performed_at' => $data['performed_at'],
                'mileage'      => $data['mileage'],
                'garage'       => $data['garage'] ?? null,
                'notes'        => $data['notes'] ?? null,
            ]);

            $maintenance->maintenanceItems()->delete();

            foreach ($data['items'] as $item) {
                $maintenance->maintenanceItems()->create([
                    'label'  => $item['label'],
                    'amount' => $item['amount'] ?? null,
                ]);
            }

            if (!empty($data['update_mileage_log'])) {
                $maintenance->motorcycle->mileageLogs()->create([
                    'mileage'   => $data['mileage'],
                    'logged_at' => $data['performed_at'],
                ]);
            }

            return $maintenance->fresh('maintenanceItems');
        });
    }

    public function delete(Maintenance $maintenance): void
    {
        DB::transaction(function () use ($maintenance) {
            foreach ($maintenance->invoices as $invoice) {
                Storage::disk('public')->delete($invoice->file_path);
                $invoice->delete();
            }

            $maintenance->delete();
        });
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

            if (!empty($data['update_mileage_log'])) {
                $motorcycle->mileageLogs()->create([
                    'mileage'   => $data['mileage'],
                    'logged_at' => $data['performed_at'],
                ]);
            }

            return $maintenance;
        });
    }
}
