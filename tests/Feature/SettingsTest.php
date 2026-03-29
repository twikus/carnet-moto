<?php

use App\Models\MaintenanceType;
use App\Models\Motorcycle;
use App\Models\User;

// Page
test('la page paramètres est accessible', function () {
    Motorcycle::factory()->create();

    $this->actingAs(User::factory()->create())
        ->get(route('settings'))
        ->assertStatus(200)
        ->assertInertia(fn ($page) => $page->component('Settings/Index'));
});

// Moto
test('les informations de la moto peuvent être mises à jour', function () {
    $motorcycle = Motorcycle::factory()->create(['make' => 'Honda', 'model' => 'CB500']);

    $this->actingAs(User::factory()->create())
        ->put(route('settings.motorcycle'), [
            'make'            => 'Yamaha',
            'model'           => 'MT-07',
            'year'            => 2022,
            'plate'           => 'AB-123-CD',
            'initial_mileage' => 5000,
        ])
        ->assertRedirect();

    $motorcycle->refresh();
    expect($motorcycle->make)->toBe('Yamaha');
    expect($motorcycle->model)->toBe('MT-07');
});

test('la mise à jour de la moto échoue sans les champs obligatoires', function () {
    Motorcycle::factory()->create();

    $this->actingAs(User::factory()->create())
        ->put(route('settings.motorcycle'), [])
        ->assertSessionHasErrors(['make', 'model', 'year', 'initial_mileage']);
});

// Discord
test('le webhook Discord peut être enregistré', function () {
    $motorcycle = Motorcycle::factory()->create();

    $this->actingAs(User::factory()->create())
        ->put(route('settings.discord'), [
            'discord_webhook_url' => 'https://discord.com/api/webhooks/123/abc',
        ])
        ->assertRedirect();

    expect($motorcycle->fresh()->discord_webhook_url)->toBe('https://discord.com/api/webhooks/123/abc');
});

test('le webhook Discord peut être vidé', function () {
    $motorcycle = Motorcycle::factory()->create(['discord_webhook_url' => 'https://discord.com/api/webhooks/123/abc']);

    $this->actingAs(User::factory()->create())
        ->put(route('settings.discord'), ['discord_webhook_url' => null])
        ->assertRedirect();

    expect($motorcycle->fresh()->discord_webhook_url)->toBeNull();
});

test('une URL invalide est refusée pour le webhook', function () {
    Motorcycle::factory()->create();

    $this->actingAs(User::factory()->create())
        ->put(route('settings.discord'), ['discord_webhook_url' => 'pas-une-url'])
        ->assertSessionHasErrors(['discord_webhook_url']);
});

// Types d'entretien
test('un type d\'entretien peut être créé', function () {
    Motorcycle::factory()->create();

    $this->actingAs(User::factory()->create())
        ->post(route('maintenance-types.store'), [
            'name'         => 'Vidange huile',
            'interval_km'  => 5000,
            'interval_days' => 365,
            'is_active'    => true,
        ])
        ->assertRedirect();

    expect(MaintenanceType::where('name', 'Vidange huile')->exists())->toBeTrue();
});

test('un type d\'entretien sans nom est refusé', function () {
    Motorcycle::factory()->create();

    $this->actingAs(User::factory()->create())
        ->post(route('maintenance-types.store'), ['name' => ''])
        ->assertSessionHasErrors(['name']);
});

test('un type d\'entretien peut être modifié', function () {
    $motorcycle = Motorcycle::factory()->create();
    $type = MaintenanceType::factory()->for($motorcycle)->create(['name' => 'Ancien nom']);

    $this->actingAs(User::factory()->create())
        ->put(route('maintenance-types.update', $type), [
            'name'      => 'Nouveau nom',
            'is_active' => true,
        ])
        ->assertRedirect();

    expect($type->fresh()->name)->toBe('Nouveau nom');
});

test('un type d\'entretien peut être supprimé', function () {
    $motorcycle = Motorcycle::factory()->create();
    $type = MaintenanceType::factory()->for($motorcycle)->create();

    $this->actingAs(User::factory()->create())
        ->delete(route('maintenance-types.destroy', $type))
        ->assertRedirect();

    expect(MaintenanceType::find($type->id))->toBeNull();
});
