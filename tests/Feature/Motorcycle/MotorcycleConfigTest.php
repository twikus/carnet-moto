<?php

use App\Models\Motorcycle;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('un utilisateur non connecté est redirigé', function () {
    $this->get('/motorcycle/create')->assertRedirect('/login');
});

test('la page de création de moto s\'affiche', function () {
    $this->actingAs(User::factory()->create())
        ->get('/motorcycle/create')
        ->assertStatus(200);
});

test('un utilisateur sans moto est redirigé vers la création', function () {
    $this->actingAs(User::factory()->create())
        ->get('/')
        ->assertRedirect('/motorcycle/create');
});

test('un utilisateur avec une moto accède au dashboard', function () {
    Motorcycle::factory()->create();

    $this->actingAs(User::factory()->create())
        ->get('/')
        ->assertStatus(200);
});

test('on peut créer une moto', function () {
    $this->actingAs(User::factory()->create())
        ->post('/motorcycle', [
            'make'            => 'Honda',
            'model'           => 'CB650R',
            'year'            => 2022,
            'plate'           => 'AB-123-CD',
            'initial_mileage' => 5000,
        ])
        ->assertRedirect('/');

    $this->assertDatabaseHas('motorcycles', [
        'make'  => 'Honda',
        'model' => 'CB650R',
        'year'  => 2022,
    ]);
});

test('la création échoue sans les champs obligatoires', function () {
    $this->actingAs(User::factory()->create())
        ->post('/motorcycle', [])
        ->assertSessionHasErrors(['make', 'model', 'year', 'initial_mileage']);
});

test('on peut créer une moto avec une photo', function () {
    Storage::fake('public');

    $this->actingAs(User::factory()->create())
        ->post('/motorcycle', [
            'make'            => 'Yamaha',
            'model'           => 'MT-07',
            'year'            => 2021,
            'initial_mileage' => 0,
            'photo'           => UploadedFile::fake()->image('moto.jpg', 800, 600),
        ])
        ->assertRedirect('/');

    $motorcycle = Motorcycle::first();
    expect($motorcycle->photo_path)->not->toBeNull();
    Storage::disk('public')->assertExists($motorcycle->photo_path);
});

test('on peut modifier une moto', function () {
    $motorcycle = Motorcycle::factory()->create(['make' => 'Honda']);

    $this->actingAs(User::factory()->create())
        ->put("/motorcycle/{$motorcycle->id}", [
            'make'            => 'Yamaha',
            'model'           => $motorcycle->model,
            'year'            => $motorcycle->year,
            'initial_mileage' => $motorcycle->initial_mileage,
        ])
        ->assertRedirect('/');

    expect($motorcycle->fresh()->make)->toBe('Yamaha');
});
