<?php

use App\Models\Maintenance;
use App\Models\MaintenanceItem;
use App\Models\MaintenanceType;
use App\Models\MileageLog;
use App\Models\Motorcycle;
use App\Models\User;

test('le dashboard est accessible quand une moto existe', function () {
    Motorcycle::factory()->create();

    $this->actingAs(User::factory()->create())
        ->get('/')
        ->assertStatus(200);
});

test('le dashboard affiche le kilométrage initial si aucun relevé', function () {
    $motorcycle = Motorcycle::factory()->create(['initial_mileage' => 5000]);

    $response = $this->actingAs(User::factory()->create())->get('/');

    $response->assertInertia(fn ($page) => $page
        ->component('Dashboard')
        ->where('dashboard.currentMileage', 5000)
    );
});

test('le dashboard affiche le dernier relevé kilométrique', function () {
    $motorcycle = Motorcycle::factory()->create(['initial_mileage' => 1000]);
    MileageLog::factory()->create(['motorcycle_id' => $motorcycle->id, 'mileage' => 8000, 'logged_at' => '2026-01-01']);
    MileageLog::factory()->create(['motorcycle_id' => $motorcycle->id, 'mileage' => 12000, 'logged_at' => '2026-03-01']);

    $response = $this->actingAs(User::factory()->create())->get('/');

    $response->assertInertia(fn ($page) => $page
        ->where('dashboard.currentMileage', 12000)
    );
});

test('le dashboard affiche la dernière intervention', function () {
    $motorcycle = Motorcycle::factory()->create();
    $maintenance = Maintenance::factory()->create([
        'motorcycle_id' => $motorcycle->id,
        'mileage'       => 10000,
        'performed_at'  => '2026-01-15',
        'garage'        => 'Moto Shop',
    ]);
    MaintenanceItem::factory()->create([
        'maintenance_id' => $maintenance->id,
        'label'          => 'Vidange',
    ]);

    $response = $this->actingAs(User::factory()->create())->get('/');

    $response->assertInertia(fn ($page) => $page
        ->where('dashboard.lastMaintenance.mileage', 10000)
        ->where('dashboard.lastMaintenance.garage', 'Moto Shop')
    );
});

test('le dashboard affiche null pour la dernière intervention si aucune', function () {
    Motorcycle::factory()->create();

    $response = $this->actingAs(User::factory()->create())->get('/');

    $response->assertInertia(fn ($page) => $page
        ->where('dashboard.lastMaintenance', null)
    );
});

test('le widget alertes classe correctement les statuts', function () {
    $motorcycle = Motorcycle::factory()->create(['initial_mileage' => 10000]);

    // Type en rouge : km dépassé
    MaintenanceType::factory()->create([
        'motorcycle_id'       => $motorcycle->id,
        'name'                => 'Vidange',
        'interval_km'         => 6000,
        'alert_threshold_km'  => 500,
        'is_active'           => true,
    ]);

    $response = $this->actingAs(User::factory()->create())->get('/');

    $response->assertInertia(fn ($page) => $page
        ->has('dashboard.upcomingMaintenances', 1)
    );
});
