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
    
    // --- ADMINISTRACIÓN ---
    Route::prefix('admin')->group(function () {
        
        // Super Admin, Admin & Manager (Gestión de Usuarios y Roles)
        Route::middleware(['role:Super Admin|Admin|Manager'])->group(function () {
            Volt::route('dashboard', 'pages.admin.dashboard')->name('admin.dashboard');
            Route::view('profile', 'profile')->name('admin.profile');
            
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

            // Módulo Detalles Horas
            Route::middleware(['can:view_hours'])->group(function () {
                Volt::route('detalles-horas', 'pages.hours.index')->name('admin.hours.index');
            });

            // Módulo Compras Materiales
            Route::middleware(['can:view_purchases'])->group(function () {
                Volt::route('compras-materiales', 'pages.purchases.index')->name('admin.purchases.index');
            });

            // Módulo Satisfacción Personal
            Route::middleware(['can:view_staff_satisfaction'])->group(function () {
                Volt::route('satisfaccion-personal', 'pages.staff-satisfaction.index')->name('admin.staff-satisfaction.index');
            });

            // Módulo Satisfacción Clientes
            Route::middleware(['can:view_client_satisfaction'])->group(function () {
                Volt::route('satisfaccion-clientes', 'pages.client-satisfaction.index')->name('admin.client-satisfaction.index');
            });

            // Módulo Tableros
            Route::middleware(['can:view_boards'])->group(function () {
                Volt::route('tableros', 'pages.boards.index')->name('admin.boards.index');
            });

            // Módulo Proyecto Automatización
            Route::middleware(['can:view_automation'])->group(function () {
                Volt::route('proyecto-automatizacion', 'pages.automation.index')->name('admin.automation.index');
            });
        });
    });

    // --- GERENCIA (Manager) ---
    // Manager tiene acceso a rutas /admin, este grupo mantiene rutas específicas de dashboard y reportes
    Route::prefix('gerente')->middleware(['role:Manager'])->group(function () {
        
        Volt::route('dashboard', 'pages.manager.dashboard')->name('manager.dashboard');
        Route::view('profile', 'profile')->name('manager.profile');
        Volt::route('produccion', 'pages.manufacturing.production-log')->name('manager.manufacturing.production.log');
        
        // Agregar RRHH si tienen permiso
        Volt::route('rrhh', 'pages.hr.factor-manager')->name('manager.hr.factors');
        
        // Gestión de Usuarios (Manager tiene acceso completo)
        Route::middleware(['can:view_users'])->group(function () {
            Route::resource('users', UserController::class)->names([
                'index' => 'manager.users.index',
                'create' => 'manager.users.create',
                'store' => 'manager.users.store',
                'show' => 'manager.users.show',
                'edit' => 'manager.users.edit',
                'update' => 'manager.users.update',
                'destroy' => 'manager.users.destroy',
            ]);
        });

        // Gestión de Roles (Manager tiene acceso completo)
        Route::middleware(['can:view_roles'])->group(function () {
            Route::resource('roles', RoleController::class)->names([
                'index' => 'manager.roles.index',
                'create' => 'manager.roles.create',
                'store' => 'manager.roles.store',
                'show' => 'manager.roles.show',
                'edit' => 'manager.roles.edit',
                'update' => 'manager.roles.update',
                'destroy' => 'manager.roles.destroy',
            ]);
            Route::get('roles/{role}/permissions', [RoleController::class, 'permissions'])
                ->name('manager.roles.permissions');
            Route::post('roles/{role}/permissions', [RoleController::class, 'updatePermissions'])
                ->name('manager.roles.permissions.update');
        });
        
        // Historial de Ventas y Presupuestos (permanecen en /gerente)
        Route::get('historial-ventas', function () {
            $sales = \App\Models\Sale::with('client')->latest()->take(50)->get();
            return view('historial-ventas', ['sales' => $sales]);
        })->name('manager.historial.ventas');
        
        Route::get('historial-presupuestos', function () {
            $budgets = \App\Models\Budget::with('client')->latest()->take(50)->get();
            return view('historial-presupuesto', ['budgets' => $budgets]);
        })->name('manager.historial.presupuesto');

        // Nuevos Módulos para Manager
        Volt::route('detalles-horas', 'pages.hours.index')->name('manager.hours.index');
        Volt::route('compras-materiales', 'pages.purchases.index')->name('manager.purchases.index');
        Volt::route('satisfaccion-personal', 'pages.staff-satisfaction.index')->name('manager.staff-satisfaction.index');
        Volt::route('satisfaccion-clientes', 'pages.client-satisfaction.index')->name('manager.client-satisfaction.index');
        Volt::route('tableros', 'pages.boards.index')->name('manager.boards.index');
        Volt::route('proyecto-automatizacion', 'pages.automation.index')->name('manager.automation.index');
    });



    // --- MÓDULOS DE SISTEMA (Rutas Amigables) - ELIMINADOS PARA USAR RUTAS POR ROL
    // Las rutas genéricas han sido reemplazadas por rutas específicas dentro de los grupos Admin y Gerente.

});

require __DIR__.'/auth.php';
