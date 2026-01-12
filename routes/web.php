<?php

use App\Http\Controllers\Auth\PasswordSetupController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::redirect('/', '/login');

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
        
        // Super Admin, Admin & Manager (Gestión de Usuarios y Roles)
        Route::middleware(['role:Super Admin|Admin|Manager'])->group(function () {
            Volt::route('dashboard', 'pages.admin.dashboard')->name('admin.dashboard');
            // Gestión de Usuarios
            Route::middleware(['can:view_users'])->group(function () {
                Route::resource('users', UserController::class);
            });

            // Gestión de Roles
            Route::middleware(['can:view_roles'])->group(function () {
                Route::resource('roles', RoleController::class);
                Route::get('roles/{role}/permissions', [RoleController::class, 'permissions'])
                    ->name('roles.permissions');
                Route::post('roles/{role}/permissions', [RoleController::class, 'updatePermissions'])
                    ->name('roles.permissions.update');
            });

            // Módulo Ventas
            Route::middleware(['can:view_sales'])->group(function () {
                Volt::route('sales/import', 'pages.sales.import-wizard')->name('admin.sales.import');
                Volt::route('clients/resolve', 'pages.clients.resolution')->name('admin.clients.resolve');
            });

            // Módulo Producción
            Route::middleware(['can:view_production'])->group(function () {
                Volt::route('manufacturing/production-log', 'pages.manufacturing.production-log')->name('admin.manufacturing.production.log');
            });

            // Módulo RRHH
            Route::middleware(['can:view_hr'])->group(function () {
                Volt::route('hr/factors', 'pages.hr.factor-manager')->name('admin.hr.factors');
            });
        });
    });

    // --- GERENCIA (Manager) ---
    // Nota: Manager ahora tiene acceso a rutas /admin, pero manteniendo este grupo para dashboards específicos si existen
    Route::prefix('manager')->middleware(['role:Manager'])->group(function () {
        Volt::route('dashboard', 'pages.manager.dashboard')->name('manager.dashboard');
        // Rutas operativas duplicadas para acceso directo si es necesario, o redirigir
        Volt::route('sales/import', 'pages.sales.import-wizard')->name('manager.sales.import');
        Volt::route('clients/resolve', 'pages.clients.resolution')->name('manager.clients.resolve');
        Volt::route('manufacturing/production-log', 'pages.manufacturing.production-log')->name('manager.manufacturing.production.log');
    });

    // --- VISOR (Viewer) ---
    Route::prefix('viewer')->middleware(['role:Viewer'])->group(function () {
        Volt::route('dashboard', 'pages.viewer.dashboard')->name('viewer.dashboard');
    });

    // --- MÓDULOS DE SISTEMA (Rutas Amigables) ---
    // Estas rutas son accesibles por cualquier rol que tenga el permiso correspondiente
    
    // Módulo Ventas
    Route::middleware(['can:view_sales'])->group(function () {
        Volt::route('ventas', 'pages.sales.import-wizard')->name('module.sales');
        Volt::route('clientes', 'pages.clients.resolution')->name('module.clients');
    });

    // Módulo Producción
    Route::middleware(['can:view_production'])->group(function () {
        Volt::route('produccion', 'pages.manufacturing.production-log')->name('module.production');
    });

    // Módulo RRHH
    Route::middleware(['can:view_hr'])->group(function () {
        Volt::route('rrhh', 'pages.hr.factor-manager')->name('module.hr');
    });

    // --- PUNTOS DE ENTRADA LEGACY (Redirecciones por compatibilidad) ---
    Route::get('sales/import', function() { return redirect()->route('module.sales'); });
    Route::get('manufacturing/production-log', function() { return redirect()->route('module.production'); });
});

require __DIR__.'/auth.php';



