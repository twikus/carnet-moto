<?php

namespace App\Services;

use App\Models\Motorcycle;
use Carbon\Carbon;

class DashboardService
{
    public function getData(Motorcycle $motorcycle): array
    {
        return [
            'currentMileage'      => $this->getCurrentMileage($motorcycle),
            'lastMaintenance'     => $this->getLastMaintenance($motorcycle),
            'upcomingMaintenances' => $this->getUpcomingMaintenances($motorcycle),
        ];
    }

    private function getCurrentMileage(Motorcycle $motorcycle): int
    {
        $lastLog = $motorcycle->mileageLogs()
            ->orderBy('logged_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->first();

        return $lastLog?->mileage ?? $motorcycle->initial_mileage;
    }

    private function getLastMaintenance(Motorcycle $motorcycle): ?array
    {
        $maintenance = $motorcycle->maintenances()
            ->with('maintenanceItems.maintenanceType')
            ->orderBy('mileage', 'desc')
            ->orderBy('performed_at', 'desc')
            ->first();

        if (! $maintenance) {
            return null;
        }

        return [
            'id'           => $maintenance->id,
            'performed_at' => $maintenance->performed_at->format('Y-m-d'),
            'mileage'      => $maintenance->mileage,
            'garage'       => $maintenance->garage,
            'labels'       => $maintenance->maintenanceItems
                ->map(fn ($item) => $item->maintenanceType?->name ?? $item->label)
                ->values(),
        ];
    }

    private function getUpcomingMaintenances(Motorcycle $motorcycle): array
    {
        $currentMileage = $this->getCurrentMileage($motorcycle);
        $today          = Carbon::today();

        $types = $motorcycle->maintenanceTypes()
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNotNull('interval_km')->orWhereNotNull('interval_days');
            })
            ->get();

        $upcoming = [];

        foreach ($types as $type) {
            // Dernière intervention pour ce type
            $lastItem = $type->maintenanceItems()
                ->join('maintenances', 'maintenance_items.maintenance_id', '=', 'maintenances.id')
                ->whereNull('maintenances.deleted_at')
                ->orderBy('maintenances.performed_at', 'desc')
                ->select('maintenance_items.*', 'maintenances.mileage as maintenance_mileage', 'maintenances.performed_at')
                ->first();

            $kmLeft   = null;
            $daysLeft = null;

            if ($lastItem && $type->interval_km) {
                $kmLeft = ($lastItem->maintenance_mileage + $type->interval_km) - $currentMileage;
            }

            if ($lastItem && $type->interval_days) {
                $daysLeft = Carbon::parse($lastItem->performed_at)->addDays($type->interval_days)->diffInDays($today, false) * -1;
            }

            $upcoming[] = [
                'id'       => $type->id,
                'name'     => $type->name,
                'km_left'  => $kmLeft,
                'days_left' => $daysLeft,
                'status'   => $this->getStatus($kmLeft, $daysLeft, $type->alert_threshold_km, $type->alert_threshold_days),
            ];
        }

        // Trier par statut (rouge d'abord) puis par km restants
        usort($upcoming, fn ($a, $b) => $this->statusOrder($a['status']) <=> $this->statusOrder($b['status']));

        return $upcoming;
    }

    private function getStatus(?int $kmLeft, ?int $daysLeft, ?int $thresholdKm, ?int $thresholdDays): string
    {
        $isRed = ($kmLeft !== null && $kmLeft <= 0)
            || ($daysLeft !== null && $daysLeft <= 0);

        if ($isRed) {
            return 'red';
        }

        $isOrange = ($kmLeft !== null && $thresholdKm !== null && $kmLeft <= $thresholdKm)
            || ($daysLeft !== null && $thresholdDays !== null && $daysLeft <= $thresholdDays);

        return $isOrange ? 'orange' : 'green';
    }

    private function statusOrder(string $status): int
    {
        return match ($status) {
            'red'    => 0,
            'orange' => 1,
            'green'  => 2,
            default  => 3,
        };
    }
}
