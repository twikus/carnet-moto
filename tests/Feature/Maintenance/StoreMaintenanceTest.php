<?php

use App\Models\Maintenance;
use App\Models\MaintenanceItem;
use App\Models\Motorcycle;
use App\Models\User;

test('un utilisateur peut accéder au formulaire de création', function () {
    Motorcycle::factory()->create();

    $this->actingAs(User::factory()->create())
        ->get(route('maintenance.create'))
        ->assertStatus(200)
        ->assertInertia(fn ($page) => $page->component('Maintenance/Create'));
});

test('une intervention est créée avec ses items', function () {
    $motorcycle = Motorcycle::factory()->create();

    $this->actingAs(User::factory()->create())
        ->post(route('maintenance.store'), [
            'performed_at' => '2026-03-01',
            'mileage'      => 12000,
            'garage'       => 'Moto Shop',
            'notes'        => 'RAS',
            'items'        => [
                ['label' => 'Vidange huile', 'amount' => 45.00],
                ['label' => 'Filtre à huile', 'amount' => 12.50],
            ],
        ])
        ->assertRedirect(route('dashboard'));

    expect(Maintenance::count())->toBe(1);
    expect(MaintenanceItem::count())->toBe(2);

    $maintenance = Maintenance::first();
    expect($maintenance->mileage)->toBe(12000);
    expect($maintenance->garage)->toBe('Moto Shop');
    expect($maintenance->maintenanceItems()->count())->toBe(2);
});

test('une intervention sans items est refusée', function () {
    Motorcycle::factory()->create();

    $this->actingAs(User::factory()->create())
        ->post(route('maintenance.store'), [
            'performed_at' => '2026-03-01',
            'mileage'      => 12000,
            'items'        => [],
        ])
        ->assertSessionHasErrors(['items']);
});

test('la date et le kilométrage sont obligatoires', function () {
    Motorcycle::factory()->create();

    $this->actingAs(User::factory()->create())
        ->post(route('maintenance.store'), [
            'items' => [['label' => 'Vidange', 'amount' => null]],
        ])
        ->assertSessionHasErrors(['performed_at', 'mileage']);
});

test('un item sans label est refusé', function () {
    Motorcycle::factory()->create();

    $this->actingAs(User::factory()->create())
        ->post(route('maintenance.store'), [
            'performed_at' => '2026-03-01',
            'mileage'      => 12000,
            'items'        => [['label' => '', 'amount' => null]],
        ])
        ->assertSessionHasErrors(['items.0.label']);
});

test('le montant de l\'item peut être nul', function () {
    Motorcycle::factory()->create();

    $this->actingAs(User::factory()->create())
        ->post(route('maintenance.store'), [
            'performed_at' => '2026-03-01',
            'mileage'      => 12000,
            'items'        => [['label' => 'Vidange', 'amount' => null]],
        ])
        ->assertRedirect(route('dashboard'));

    expect(MaintenanceItem::first()->amount)->toBeNull();
});

test('le kilométrage est refusé si inférieur à une intervention antérieure', function () {
    $motorcycle = Motorcycle::factory()->create();
    Maintenance::factory()->for($motorcycle)->create([
        'performed_at' => '2026-03-27',
        'mileage'      => 27800,
    ]);

    $this->actingAs(User::factory()->create())
        ->post(route('maintenance.store'), [
            'performed_at' => '2026-03-29',
            'mileage'      => 26700,
            'items'        => [['label' => 'Vidange', 'amount' => null]],
        ])
        ->assertSessionHasErrors(['mileage']);
});

test('le kilométrage est refusé si supérieur à une intervention postérieure', function () {
    $motorcycle = Motorcycle::factory()->create();
    Maintenance::factory()->for($motorcycle)->create([
        'performed_at' => '2026-03-29',
        'mileage'      => 26700,
    ]);

    $this->actingAs(User::factory()->create())
        ->post(route('maintenance.store'), [
            'performed_at' => '2026-03-27',
            'mileage'      => 27800,
            'items'        => [['label' => 'Vidange', 'amount' => null]],
        ])
        ->assertSessionHasErrors(['mileage']);
});

test('deux interventions le même jour avec des km différents sont acceptées', function () {
    $motorcycle = Motorcycle::factory()->create();
    Maintenance::factory()->for($motorcycle)->create([
        'performed_at' => '2026-03-27',
        'mileage'      => 27800,
    ]);

    $this->actingAs(User::factory()->create())
        ->post(route('maintenance.store'), [
            'performed_at' => '2026-03-27',
            'mileage'      => 27700,
            'items'        => [['label' => 'Vidange', 'amount' => null]],
        ])
        ->assertRedirect(route('dashboard'));
});
