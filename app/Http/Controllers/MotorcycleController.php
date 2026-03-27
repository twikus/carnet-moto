<?php

namespace App\Http\Controllers;

use App\Models\Motorcycle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class MotorcycleController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Motorcycle/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'make'            => ['required', 'string', 'max:100'],
            'model'           => ['required', 'string', 'max:100'],
            'year'            => ['required', 'integer', 'min:1900', 'max:' . (date('Y') + 1)],
            'plate'           => ['nullable', 'string', 'max:20'],
            'initial_mileage' => ['required', 'integer', 'min:0'],
            'photo'           => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
        ]);

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('motorcycles', 'public');
        }

        Motorcycle::create([
            'make'            => $validated['make'],
            'model'           => $validated['model'],
            'year'            => $validated['year'],
            'plate'           => $validated['plate'] ?? null,
            'initial_mileage' => $validated['initial_mileage'],
            'photo_path'      => $photoPath,
        ]);

        return redirect()->route('dashboard');
    }

    public function edit(Motorcycle $motorcycle): Response
    {
        return Inertia::render('Motorcycle/Edit', [
            'motorcycle' => $motorcycle,
        ]);
    }

    public function update(Request $request, Motorcycle $motorcycle): RedirectResponse
    {
        $validated = $request->validate([
            'make'            => ['required', 'string', 'max:100'],
            'model'           => ['required', 'string', 'max:100'],
            'year'            => ['required', 'integer', 'min:1900', 'max:' . (date('Y') + 1)],
            'plate'           => ['nullable', 'string', 'max:20'],
            'initial_mileage' => ['required', 'integer', 'min:0'],
            'photo'           => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
        ]);

        if ($request->hasFile('photo')) {
            if ($motorcycle->photo_path) {
                Storage::disk('public')->delete($motorcycle->photo_path);
            }
            $validated['photo_path'] = $request->file('photo')->store('motorcycles', 'public');
        }

        unset($validated['photo']);

        $motorcycle->update($validated);

        return redirect()->route('dashboard');
    }
}
