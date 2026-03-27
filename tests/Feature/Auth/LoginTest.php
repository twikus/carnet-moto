<?php

use App\Models\User;

test('la page de connexion s\'affiche', function () {
    $this->get('/login')->assertStatus(200);
});

test('un utilisateur peut se connecter', function () {
    $user = User::factory()->create();

    $this->post('/login', [
        'email'    => $user->email,
        'password' => 'password',
    ])->assertRedirect('/');

    $this->assertAuthenticatedAs($user);
});

test('un mauvais mot de passe est refusé', function () {
    $user = User::factory()->create();

    $this->post('/login', [
        'email'    => $user->email,
        'password' => 'mauvais-mdp',
    ])->assertSessionHasErrors('email');

    $this->assertGuest();
});

test('un utilisateur non connecté est redirigé vers /login', function () {
    $this->get('/')->assertRedirect('/login');
});

test('un utilisateur connecté peut se déconnecter', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/logout')
        ->assertRedirect('/login');

    $this->assertGuest();
});

test('le throttle bloque après 5 tentatives', function () {
    $user = User::factory()->create();

    foreach (range(1, 5) as $i) {
        $this->post('/login', ['email' => $user->email, 'password' => 'wrong']);
    }

    $this->post('/login', ['email' => $user->email, 'password' => 'wrong'])
        ->assertSessionHasErrors('email');
});
