<?php

namespace App\Http\Controllers;

use App\Models\Motorcycle;
use App\Services\DashboardService;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(private DashboardService $dashboardService) {}

    public function index(): Response
    {
        $motorcycle = Motorcycle::first();

        return Inertia::render('Dashboard', [
            'motorcycle' => $motorcycle,
            'dashboard'  => $this->dashboardService->getData($motorcycle),
        ]);
    }
}
