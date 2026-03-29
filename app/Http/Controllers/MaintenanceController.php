<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMaintenanceRequest;
use App\Http\Requests\UpdateMaintenanceRequest;
use App\Models\Maintenance;
use App\Models\Motorcycle;
use App\Services\MaintenanceService;
use Inertia\Inertia;
use Inertia\Response;

class MaintenanceController extends Controller
{
    public function __construct(private MaintenanceService $maintenanceService) {}

    public function show(Maintenance $maintenance): Response
    {
        $motorcycle = Motorcycle::first();
        $maintenance = $this->maintenanceService->findForMotorcycle($motorcycle, $maintenance);

        return Inertia::render('Maintenance/Show', [
            'motorcycle'  => $motorcycle,
            'maintenance' => $maintenance,
        ]);
    }

    public function edit(Maintenance $maintenance): Response
    {
        $motorcycle = Motorcycle::first();
        $maintenance->load('maintenanceItems');

        return Inertia::render('Maintenance/Edit', [
            'motorcycle'     => $motorcycle,
            'maintenance'    => $maintenance,
            'currentMileage' => $motorcycle->current_mileage,
        ]);
    }

    public function update(UpdateMaintenanceRequest $request, Maintenance $maintenance)
    {
        $this->maintenanceService->update($maintenance, $request->validated());

        return redirect()->route('maintenance.show', $maintenance);
    }

    public function destroy(Maintenance $maintenance)
    {
        $this->maintenanceService->delete($maintenance);

        return redirect()->route('maintenance.index');
    }

    public function index(): Response
    {
        $motorcycle = Motorcycle::first();
        $maintenances = $this->maintenanceService->listForMotorcycle($motorcycle);

        return Inertia::render('Maintenance/Index', [
            'motorcycle'   => $motorcycle,
            'maintenances' => $maintenances,
        ]);
    }

    public function create(): Response
    {
        $motorcycle = Motorcycle::first();

        return Inertia::render('Maintenance/Create', [
            'currentMileage' => $motorcycle->current_mileage,
        ]);
    }

    public function store(StoreMaintenanceRequest $request)
    {
        $motorcycle = Motorcycle::first();

        $this->maintenanceService->create($motorcycle, $request->validated());

        return redirect()->route('dashboard');
    }
}
