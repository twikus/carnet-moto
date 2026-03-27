<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\MotorcycleController;
use App\Http\Middleware\EnsureMotorcycleExists;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Auth
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
});

Route::post('/logout', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');

// App
Route::middleware('auth')->group(function () {
    // Configuration moto (accessible même sans moto existante)
    Route::get('/motorcycle/create', [MotorcycleController::class, 'create'])->name('motorcycle.create');
    Route::post('/motorcycle', [MotorcycleController::class, 'store'])->name('motorcycle.store');
    Route::get('/motorcycle/{motorcycle}/edit', [MotorcycleController::class, 'edit'])->name('motorcycle.edit');
    Route::put('/motorcycle/{motorcycle}', [MotorcycleController::class, 'update'])->name('motorcycle.update');

    // Routes protégées : nécessitent une moto configurée
    Route::middleware(EnsureMotorcycleExists::class)->group(function () {
        Route::get('/', fn () => Inertia::render('Dashboard'))->name('dashboard');
    });
});
