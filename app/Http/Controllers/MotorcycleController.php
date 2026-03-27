<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMotorcycleRequest;
use App\Http\Requests\UpdateMotorcycleRequest;
use App\Models\Motorcycle;
use App\Services\MotorcycleService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class MotorcycleController extends Controller
{
    public function __construct(private MotorcycleService $motorcycleService) {}

    public function create(): Response
    {
        return Inertia::render('Motorcycle/Create');
    }

    public function store(StoreMotorcycleRequest $request): RedirectResponse
    {
        $this->motorcycleService->create($request->validated(), $request->file('photo'));

        return redirect()->route('dashboard');
    }

    public function edit(Motorcycle $motorcycle): Response
    {
        return Inertia::render('Motorcycle/Edit', [
            'motorcycle' => $motorcycle,
        ]);
    }

    public function update(UpdateMotorcycleRequest $request, Motorcycle $motorcycle): RedirectResponse
    {
        $this->motorcycleService->update($motorcycle, $request->validated(), $request->file('photo'));

        return redirect()->route('dashboard');
    }
}
