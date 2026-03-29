<?php

use App\Models\Maintenance;
use App\Models\MaintenanceItem;
use App\Models\Motorcycle;
use App\Models\User;

test('la page détail est accessible', function () {
    $motorcycle = Motorcycle::factory()->create();
    $maintenance = Maintenance::factory()->for($motorcycle)->create();

    $this->actingAs(User::factory()->create())
        ->get(route('maintenance.show', $maintenance))
        ->assertStatus(200)
        ->assertInertia(fn ($page) => $page->component('Maintenance/Show'));
});

test('le détail contient les bonnes données', function () {
    $motorcycle = Motorcycle::factory()->create();
    $maintenance = Maintenance::factory()->for($motorcycle)->create([
        'performed_at' => '2026-03-01',
        'mileage'      => 12000,
        'garage'       => 'Moto Shop',
        'notes'        => 'RAS',
    ]);
    MaintenanceItem::factory()->for($maintenance)->create([
        'label'  => 'Vidange',
        'amount' => 45.00,
    ]);

    $this->actingAs(User::factory()->create())
        ->get(route('maintenance.show', $maintenance))
        ->assertInertia(fn ($page) => $page
            ->component('Maintenance/Show')
            ->where('maintenance.mileage', 12000)
            ->where('maintenance.garage', 'Moto Shop')
            ->where('maintenance.notes', 'RAS')
            ->where('maintenance.maintenance_items.0.label', 'Vidange')
        );
});

test('une maintenance inconnue retourne 404', function () {
    Motorcycle::factory()->create();

    $this->actingAs(User::factory()->create())
        ->get(route('maintenance.show', 'uuid-inexistant'))
        ->assertStatus(404);
});

test('les cards de la liste sont cliquables', function () {
    $motorcycle = Motorcycle::factory()->create();
    $maintenance = Maintenance::factory()->for($motorcycle)->create();

    $this->actingAs(User::factory()->create())
        ->get(route('maintenance.index'))
        ->assertInertia(fn ($page) => $page
            ->component('Maintenance/Index')
            ->where('maintenances.0.id', $maintenance->id)
        );
});
