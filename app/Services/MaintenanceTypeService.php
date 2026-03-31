<?php

namespace App\Services;

use App\Models\MaintenanceType;
use App\Models\Motorcycle;

class MaintenanceTypeService
{
    public function create(Motorcycle $motorcycle, array $data): MaintenanceType
    {
        return $motorcycle->maintenanceTypes()->create($data);
    }

    public function update(MaintenanceType $maintenanceType, array $data): MaintenanceType
    {
        $maintenanceType->update($data);

        return $maintenanceType;
    }

    public function delete(MaintenanceType $maintenanceType): void
    {
        $maintenanceType->delete();
    }

    public function buildTestAlertMessage(MaintenanceType $maintenanceType, Motorcycle $motorcycle): string
    {
        $lines = ["🏍️ **{$motorcycle->make} {$motorcycle->model}** — Test d'alerte entretien"];
        $lines[] = "🔧 **{$maintenanceType->name}**";

        if ($maintenanceType->interval_km) {
            $lines[] = "📏 Intervalle : " . number_format($maintenanceType->interval_km, 0, ',', ' ') . " km";
        }
        if ($maintenanceType->interval_days) {
            $lines[] = "📅 Intervalle : {$maintenanceType->interval_days} jour(s)";
        }
        if ($maintenanceType->alert_threshold_km) {
            $lines[] = "⚠️ Seuil d'alerte : " . number_format($maintenanceType->alert_threshold_km, 0, ',', ' ') . " km avant échéance";
        }
        if ($maintenanceType->alert_threshold_days) {
            $lines[] = "⚠️ Seuil d'alerte : {$maintenanceType->alert_threshold_days} jour(s) avant échéance";
        }

        return implode("\n", $lines);
    }
}
