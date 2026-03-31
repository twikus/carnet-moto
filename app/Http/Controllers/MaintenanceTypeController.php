<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMaintenanceTypeRequest;
use App\Http\Requests\UpdateMaintenanceTypeRequest;
use App\Models\MaintenanceType;
use App\Models\Motorcycle;
use App\Services\DiscordService;
use App\Services\MaintenanceTypeService;

class MaintenanceTypeController extends Controller
{
    public function __construct(
        private MaintenanceTypeService $maintenanceTypeService,
        private DiscordService $discordService,
    ) {}

    public function store(StoreMaintenanceTypeRequest $request)
    {
        $this->maintenanceTypeService->create(Motorcycle::first(), $request->validated());

        return back()->with('success', 'Type d\'entretien ajouté.');
    }

    public function update(UpdateMaintenanceTypeRequest $request, MaintenanceType $maintenanceType)
    {
        $this->maintenanceTypeService->update($maintenanceType, $request->validated());

        return back()->with('success', 'Type d\'entretien mis à jour.');
    }

    public function destroy(MaintenanceType $maintenanceType)
    {
        $this->maintenanceTypeService->delete($maintenanceType);

        return back()->with('success', 'Type d\'entretien supprimé.');
    }

    public function testAlert(MaintenanceType $maintenanceType)
    {
        $motorcycle = Motorcycle::first();

        if (!$motorcycle->discord_webhook_url) {
            return back()->with('error', 'Aucun webhook Discord configuré.');
        }

        $message = $this->maintenanceTypeService->buildTestAlertMessage($maintenanceType, $motorcycle);
        $ok      = $this->discordService->send($motorcycle->discord_webhook_url, $message);

        return $ok
            ? back()->with('success', "Alerte test envoyée pour « {$maintenanceType->name} ».")
            : back()->with('error', 'Impossible de joindre le webhook Discord.');
    }
}
