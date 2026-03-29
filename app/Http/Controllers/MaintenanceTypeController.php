<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMaintenanceTypeRequest;
use App\Http\Requests\UpdateMaintenanceTypeRequest;
use App\Models\MaintenanceType;
use App\Models\Motorcycle;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class MaintenanceTypeController extends Controller
{
    public function store(StoreMaintenanceTypeRequest $request)
    {
        Motorcycle::first()->maintenanceTypes()->create($request->validated());

        return back()->with('success', 'Type d\'entretien ajouté.');
    }

    public function update(UpdateMaintenanceTypeRequest $request, MaintenanceType $maintenanceType)
    {
        $maintenanceType->update($request->validated());

        return back()->with('success', 'Type d\'entretien mis à jour.');
    }

    public function destroy(MaintenanceType $maintenanceType)
    {
        $maintenanceType->delete();

        return back()->with('success', 'Type d\'entretien supprimé.');
    }

    public function testAlert(MaintenanceType $maintenanceType)
    {
        $motorcycle = Motorcycle::first();
        $url        = $motorcycle->discord_webhook_url;

        if (!$url) {
            return back()->with('error', 'Aucun webhook Discord configuré.');
        }

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

        try {
            $response = Http::post($url, ['content' => implode("\n", $lines)]);

            if ($response->successful()) {
                return back()->with('success', "Alerte test envoyée pour « {$maintenanceType->name} ».");
            }

            return back()->with('error', 'Erreur Discord : ' . $response->status());
        } catch (ConnectionException) {
            return back()->with('error', 'Impossible de joindre le webhook Discord.');
        }
    }
}
