<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMaintenanceRequest;
use App\Models\Motorcycle;
use App\Services\MaintenanceService;
use Inertia\Inertia;
use Inertia\Response;

class MaintenanceController extends Controller
{
    public function __construct(private MaintenanceService $maintenanceService) {}

    public function create(): Response
    {
        return Inertia::render('Maintenance/Create');
    }

    public function store(StoreMaintenanceRequest $request)
    {
        $motorcycle = Motorcycle::first();

        $this->maintenanceService->create($motorcycle, $request->validated());

        return redirect()->route('dashboard');
    }
}
