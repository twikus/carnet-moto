<?php

use App\Models\Maintenance;
use App\Models\MaintenanceItem;
use App\Models\Motorcycle;
use App\Models\User;

test('la page historique est accessible', function () {
    Motorcycle::factory()->create();

    $this->actingAs(User::factory()->create())
        ->get(route('maintenance.index'))
        ->assertStatus(200)
        ->assertInertia(fn ($page) => $page->component('Maintenance/Index'));
});

test('les interventions sont triées par km puis par date', function () {
    $motorcycle = Motorcycle::factory()->create();

    $m1 = Maintenance::factory()->for($motorcycle)->create([
        'mileage'      => 10000,
        'performed_at' => '2026-01-15',
    ]);
    $m2 = Maintenance::factory()->for($motorcycle)->create([
        'mileage'      => 8000,
        'performed_at' => '2026-03-01',
    ]);
    $m3 = Maintenance::factory()->for($motorcycle)->create([
        'mileage'      => 10000,
        'performed_at' => '2026-01-10',
    ]);

    $this->actingAs(User::factory()->create())
        ->get(route('maintenance.index'))
        ->assertInertia(fn ($page) => $page
            ->component('Maintenance/Index')
            ->where('maintenances.0.id', $m2->id)
            ->where('maintenances.1.id', $m3->id)
            ->where('maintenances.2.id', $m1->id)
        );
});

test('les items sont chargés avec les interventions', function () {
    $motorcycle = Motorcycle::factory()->create();
    $maintenance = Maintenance::factory()->for($motorcycle)->create();
    MaintenanceItem::factory()->for($maintenance)->create(['label' => 'Vidange']);

    $this->actingAs(User::factory()->create())
        ->get(route('maintenance.index'))
        ->assertInertia(fn ($page) => $page
            ->component('Maintenance/Index')
            ->where('maintenances.0.maintenance_items.0.label', 'Vidange')
        );
});

test('la liste est vide si aucune intervention', function () {
    Motorcycle::factory()->create();

    $this->actingAs(User::factory()->create())
        ->get(route('maintenance.index'))
        ->assertInertia(fn ($page) => $page
            ->component('Maintenance/Index')
            ->where('maintenances', [])
        );
});
