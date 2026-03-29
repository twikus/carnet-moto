<?php

use App\Models\Maintenance;
use App\Models\MaintenanceItem;
use App\Models\Motorcycle;
use App\Models\User;

// SCRUM-16 — Modifier
test('la page édition est accessible', function () {
    $motorcycle = Motorcycle::factory()->create();
    $maintenance = Maintenance::factory()->for($motorcycle)->create();
    MaintenanceItem::factory()->for($maintenance)->create();

    $this->actingAs(User::factory()->create())
        ->get(route('maintenance.edit', $maintenance))
        ->assertStatus(200)
        ->assertInertia(fn ($page) => $page->component('Maintenance/Edit'));
});

test('une intervention peut être modifiée', function () {
    $motorcycle = Motorcycle::factory()->create();
    $maintenance = Maintenance::factory()->for($motorcycle)->create(['mileage' => 10000]);
    MaintenanceItem::factory()->for($maintenance)->create(['label' => 'Ancien item']);

    $this->actingAs(User::factory()->create())
        ->put(route('maintenance.update', $maintenance), [
            'performed_at' => '2026-04-01',
            'mileage'      => 12000,
            'garage'       => 'Nouveau garage',
            'notes'        => null,
            'items'        => [
                ['label' => 'Nouvel item', 'amount' => 50.00],
            ],
        ])
        ->assertRedirect(route('maintenance.show', $maintenance));

    $maintenance->refresh();
    expect($maintenance->mileage)->toBe(12000);
    expect($maintenance->garage)->toBe('Nouveau garage');
    expect($maintenance->maintenanceItems()->count())->toBe(1);
    expect($maintenance->maintenanceItems()->first()->label)->toBe('Nouvel item');
});

test('les anciens items sont remplacés à la modification', function () {
    $motorcycle = Motorcycle::factory()->create();
    $maintenance = Maintenance::factory()->for($motorcycle)->create();
    MaintenanceItem::factory()->for($maintenance)->count(3)->create();

    $this->actingAs(User::factory()->create())
        ->put(route('maintenance.update', $maintenance), [
            'performed_at' => '2026-04-01',
            'mileage'      => 12000,
            'items'        => [['label' => 'Seul item', 'amount' => null]],
        ]);

    expect($maintenance->maintenanceItems()->count())->toBe(1);
});

test('la modification échoue sans les champs obligatoires', function () {
    $motorcycle = Motorcycle::factory()->create();
    $maintenance = Maintenance::factory()->for($motorcycle)->create();

    $this->actingAs(User::factory()->create())
        ->put(route('maintenance.update', $maintenance), [])
        ->assertSessionHasErrors(['performed_at', 'mileage', 'items']);
});

// SCRUM-17 — Supprimer
test('une intervention peut être supprimée', function () {
    $motorcycle = Motorcycle::factory()->create();
    $maintenance = Maintenance::factory()->for($motorcycle)->create();

    $this->actingAs(User::factory()->create())
        ->delete(route('maintenance.destroy', $maintenance))
        ->assertRedirect(route('maintenance.index'));

    expect(Maintenance::find($maintenance->id))->toBeNull();
    expect(Maintenance::withTrashed()->find($maintenance->id))->not->toBeNull();
});

test('la suppression est un soft delete', function () {
    $motorcycle = Motorcycle::factory()->create();
    $maintenance = Maintenance::factory()->for($motorcycle)->create();

    $this->actingAs(User::factory()->create())
        ->delete(route('maintenance.destroy', $maintenance));

    expect(Maintenance::withTrashed()->find($maintenance->id)->deleted_at)->not->toBeNull();
});

test('la modification échoue si le km est incohérent avec une intervention antérieure', function () {
    $motorcycle = Motorcycle::factory()->create();

    Maintenance::factory()->for($motorcycle)->create([
        'performed_at' => '2026-03-27',
        'mileage'      => 27800,
    ]);

    $maintenance = Maintenance::factory()->for($motorcycle)->create([
        'performed_at' => '2026-03-29',
        'mileage'      => 28000,
    ]);
    MaintenanceItem::factory()->for($maintenance)->create();

    $this->actingAs(User::factory()->create())
        ->put(route('maintenance.update', $maintenance), [
            'performed_at' => '2026-03-29',
            'mileage'      => 26700,
            'items'        => [['label' => 'Vidange', 'amount' => null]],
        ])
        ->assertSessionHasErrors(['mileage']);
});

test('la modification ne se bloque pas sur sa propre valeur', function () {
    $motorcycle = Motorcycle::factory()->create();
    $maintenance = Maintenance::factory()->for($motorcycle)->create([
        'performed_at' => '2026-03-27',
        'mileage'      => 27800,
    ]);
    MaintenanceItem::factory()->for($maintenance)->create();

    $this->actingAs(User::factory()->create())
        ->put(route('maintenance.update', $maintenance), [
            'performed_at' => '2026-03-27',
            'mileage'      => 27800,
            'items'        => [['label' => 'Vidange', 'amount' => null]],
        ])
        ->assertRedirect(route('maintenance.show', $maintenance));
});
