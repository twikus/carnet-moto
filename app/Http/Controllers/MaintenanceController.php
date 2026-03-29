<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMaintenanceRequest;
use App\Models\Motorcycle;
use App\Services\MaintenanceService;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class MaintenanceController extends Controller
{
    public function __construct(private MaintenanceService $maintenanceService) {}

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
        return Inertia::render('Maintenance/Create');
    }

    public function store(StoreMaintenanceRequest $request)
    {
        $motorcycle = Motorcycle::first();

        $this->maintenanceService->create($motorcycle, $request->validated());

        return redirect()->route('dashboard');
    }
}
