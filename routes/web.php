<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\MaintenanceController;
use App\Http\Controllers\MaintenanceTypeController;
use App\Http\Controllers\MotorcycleController;
use App\Http\Controllers\SettingsController;
use App\Http\Middleware\EnsureMotorcycleExists;
use Illuminate\Support\Facades\Route;

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
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        // Factures
        Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoice.index');
        Route::get('/invoices/create', [InvoiceController::class, 'create'])->name('invoice.create');
        Route::post('/invoices', [InvoiceController::class, 'store'])->name('invoice.store');
        Route::get('/invoices/{invoice}/processing', [InvoiceController::class, 'processing'])->name('invoice.processing');
        Route::post('/invoices/{invoice}/retry', [InvoiceController::class, 'retry'])->name('invoice.retry');
        Route::delete('/invoices/{invoice}', [InvoiceController::class, 'destroy'])->name('invoice.destroy');
        Route::get('/invoices/{invoice}/review', [InvoiceController::class, 'review'])->name('invoice.review');
        Route::post('/invoices/{invoice}/confirm', [InvoiceController::class, 'confirm'])->name('invoice.confirm');
        Route::get('/invoices/{invoice}/image', [InvoiceController::class, 'image'])->name('invoice.image');

        Route::get('/maintenances', [MaintenanceController::class, 'index'])->name('maintenance.index');
        Route::get('/maintenances/create', [MaintenanceController::class, 'create'])->name('maintenance.create');
        Route::post('/maintenances', [MaintenanceController::class, 'store'])->name('maintenance.store');
        Route::get('/maintenances/{maintenance}', [MaintenanceController::class, 'show'])->name('maintenance.show');
        Route::get('/maintenances/{maintenance}/edit', [MaintenanceController::class, 'edit'])->name('maintenance.edit');
        Route::put('/maintenances/{maintenance}', [MaintenanceController::class, 'update'])->name('maintenance.update');
        Route::delete('/maintenances/{maintenance}', [MaintenanceController::class, 'destroy'])->name('maintenance.destroy');

        // Settings
        Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
        Route::put('/settings/motorcycle', [SettingsController::class, 'updateMotorcycle'])->name('settings.motorcycle');
        Route::put('/settings/discord', [SettingsController::class, 'updateDiscord'])->name('settings.discord');
        Route::post('/settings/discord/test', [SettingsController::class, 'testDiscord'])->name('settings.discord.test');

        // Maintenance types
        Route::post('/settings/maintenance-types', [MaintenanceTypeController::class, 'store'])->name('maintenance-types.store');
        Route::put('/settings/maintenance-types/{maintenanceType}', [MaintenanceTypeController::class, 'update'])->name('maintenance-types.update');
        Route::delete('/settings/maintenance-types/{maintenanceType}', [MaintenanceTypeController::class, 'destroy'])->name('maintenance-types.destroy');
        Route::post('/settings/maintenance-types/{maintenanceType}/test-alert', [MaintenanceTypeController::class, 'testAlert'])->name('maintenance-types.test-alert');
    });
});
