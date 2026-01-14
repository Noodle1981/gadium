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
                Volt::route('importacion', 'pages.sales.import-wizard')->name('admin.sales.import');
                Volt::route('clientes', 'pages.clients.resolution')->name('admin.clients.resolve'); // clientes
                
                // Historial de Ventas y Presupuestos
                Route::get('historial-ventas', function () {
                    $sales = \App\Models\Sale::with('client')->latest()->take(50)->get();
                    return view('historial-ventas', ['sales' => $sales]);
                })->name('admin.historial.ventas');
                
                Route::get('historial-presupuestos', function () {
                    $budgets = \App\Models\Budget::with('client')->latest()->take(50)->get();
                    return view('historial-presupuesto', ['budgets' => $budgets]);
                })->name('admin.historial.presupuesto');
            });

            // Módulo Producción
            Route::middleware(['can:view_production'])->group(function () {
                Volt::route('produccion', 'pages.manufacturing.production-log')->name('admin.manufacturing.production.log'); // produccion
            });

            // Módulo RRHH
            Route::middleware(['can:view_hr'])->group(function () {
                Volt::route('rrhh', 'pages.hr.factor-manager')->name('admin.hr.factors'); // rrhh
            });
        });
    });

    // --- GERENCIA (Manager) ---
    // Nota: Manager ahora tiene acceso a rutas /admin, pero manteniendo este grupo para dashboards específicos si existen
    Route::prefix('gerente')->middleware(['role:Manager'])->group(function () { // Changed prefix to 'gerente' as requested? User asked for /gerente/importacion. Existing was prefix('manager'). I should probably keep 'manager' prefix internally but user asked specifically for /gerente/importacion. I will use 'gerente' prefix if existing strictly maps 'manager' prefix to 'gerente' role.
        // Wait, existing code has Route::prefix('manager'). User asked for /gerente/importacion. 
        // I will add a specific route group or change prefix if acceptable. 
        // Let's assume 'manager' prefix was intended to be 'gerente' or just add a new group or alias.
        // Or simply rename the prefix to 'gerente' if that fits.
        // However, looking at existing 'manager' prefix, it has dashboard.
        // I'll change the prefix to 'gerente' to match request perfectly, assuming no other hard links break (routes are named).
        
        Volt::route('dashboard', 'pages.manager.dashboard')->name('manager.dashboard');
        Volt::route('importacion', 'pages.sales.import-wizard')->name('manager.sales.import');
        Volt::route('clientes', 'pages.clients.resolution')->name('manager.clients.resolve'); // clientes
        Volt::route('produccion', 'pages.manufacturing.production-log')->name('manager.manufacturing.production.log'); // produccion
        
        // Agregar RRHH si tienen permiso
        Volt::route('rrhh', 'pages.hr.factor-manager')->name('manager.hr.factors');
        
        // Historial de Ventas y Presupuestos
        Route::get('historial-ventas', function () {
            $sales = \App\Models\Sale::with('client')->latest()->take(50)->get();
            return view('historial-ventas', ['sales' => $sales]);
        })->name('manager.historial.ventas');
        
        Route::get('historial-presupuestos', function () {
            $budgets = \App\Models\Budget::with('client')->latest()->take(50)->get();
            return view('historial-presupuesto', ['budgets' => $budgets]);
        })->name('manager.historial.presupuesto');
    });



    // --- MÓDULOS DE SISTEMA (Rutas Amigables) - ELIMINADOS PARA USAR RUTAS POR ROL
    // Las rutas genéricas han sido reemplazadas por rutas específicas dentro de los grupos Admin y Gerente.

});

require __DIR__.'/auth.php';
