<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMaintenanceTypeRequest;
use App\Http\Requests\UpdateMaintenanceTypeRequest;
use App\Models\MaintenanceType;
use App\Models\Motorcycle;

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
}
