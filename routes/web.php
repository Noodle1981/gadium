<?php

use App\Http\Controllers\Auth\PasswordSetupController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::view('/', 'welcome');

// Rutas de configuración de contraseña (firmadas)
Route::get('/setup-password', [PasswordSetupController::class, 'show'])
    ->name('password.setup');
Route::post('/setup-password', [PasswordSetupController::class, 'store'])
    ->name('password.setup.store');

Route::middleware(['auth', 'verified', 'role.redirect'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
    Route::view('profile', 'profile')->name('profile');
    
    // --- ADMINISTRACIÓN ---
    Route::prefix('admin')->group(function () {
        
        // Super Admin & Admin
        Route::middleware(['role:Super Admin|Admin'])->group(function () {
            Volt::route('dashboard', 'pages.admin.dashboard')->name('admin.dashboard');
            Route::resource('users', UserController::class);
            Volt::route('sales/import', 'pages.sales.import-wizard')->name('admin.sales.import');
            Volt::route('clients/resolve', 'pages.clients.resolution')->name('admin.clients.resolve');
            Volt::route('manufacturing/production-log', 'pages.manufacturing.production-log')->name('admin.manufacturing.production.log');
            Volt::route('hr/factors', 'pages.hr.factor-manager')->name('admin.hr.factors');
        });

        // Solo Super Admin
        Route::middleware(['role:Super Admin'])->group(function () {
            Route::resource('roles', RoleController::class);
            Route::get('roles/{role}/permissions', [RoleController::class, 'permissions'])
                ->name('roles.permissions');
            Route::post('roles/{role}/permissions', [RoleController::class, 'updatePermissions'])
                ->name('roles.permissions.update');
        });
    });

    // --- GERENCIA (Manager) ---
    Route::prefix('manager')->middleware(['role:Manager'])->group(function () {
        Volt::route('dashboard', 'pages.manager.dashboard')->name('manager.dashboard');
        Volt::route('sales/import', 'pages.sales.import-wizard')->name('manager.sales.import');
        Volt::route('clients/resolve', 'pages.clients.resolution')->name('manager.clients.resolve');
        Volt::route('manufacturing/production-log', 'pages.manufacturing.production-log')->name('manager.manufacturing.production.log');
    });

    // --- VISOR (Viewer) ---
    Route::prefix('viewer')->middleware(['role:Viewer'])->group(function () {
        Volt::route('dashboard', 'pages.viewer.dashboard')->name('viewer.dashboard');
    });

    // --- PUNTOS DE ENTRADA GENÉRICOS (Redirigidos por RoleRedirect) ---
    Route::get('sales/import', function() { return redirect()->route('dashboard'); })->name('sales.import');
    Route::get('clients/resolve', function() { return redirect()->route('dashboard'); })->name('clients.resolve');
    Route::get('manufacturing/production-log', function() { return redirect()->route('dashboard'); })->name('manufacturing.production.log');
});

require __DIR__.'/auth.php';



