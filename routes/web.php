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

// ============================================================================
// RUTAS UNIFICADAS - BASADAS EN PERMISOS
// ============================================================================
// Todas las rutas usan el prefijo /app y son controladas por permisos,
// no por nombres de rol. Esto elimina duplicación y simplifica el mantenimiento.
// ============================================================================

Route::middleware(['auth'])->prefix('app')->group(function () {

    // ========================================
    // DASHBOARD Y PERFIL
    // ========================================

    Route::middleware(['can:view_dashboards'])->group(function () {
        Volt::route('dashboard', 'pages.admin.dashboard')->name('app.dashboard');
        Volt::route('intelligence', 'pages.manager.intelligence')->name('app.intelligence');
        Volt::route('audit', 'audit-log')->name('app.audit');
    });

    // Perfil - todos los usuarios autenticados
    Route::view('profile', 'profile')->name('app.profile');

    // ========================================
    // MÓDULO VENTAS
    // ========================================

    Route::prefix('sales')->middleware(['can:view_sales'])->group(function () {
        Volt::route('/', 'pages.sales.history')->name('app.sales.index');
        Volt::route('history', 'pages.sales.history')->name('app.sales.history');
        Volt::route('import', 'pages.sales.import-wizard')->name('app.sales.import');

        Route::middleware(['can:create_sales'])->group(function () {
            Volt::route('create', 'pages.sales.manual-create')->name('app.sales.create');
        });

        Route::middleware(['can:edit_sales'])->group(function () {
            Volt::route('edit/{sale}', 'pages.sales.manual-edit')->name('app.sales.edit');
        });

        // Resolución de clientes
        Volt::route('clients/resolve', 'pages.clients.resolution')->name('app.clients.resolve');

        // Catálogo de clientes
        Volt::route('catalogs/clients', 'pages.sales.catalogs.clients.index')->name('app.sales.catalogs.clients');
    });

    // ========================================
    // MÓDULO PRESUPUESTOS
    // ========================================

    Route::prefix('budgets')->middleware(['can:view_budgets'])->group(function () {
        Volt::route('/', 'pages.budget.history')->name('app.budgets.index');
        Volt::route('history', 'pages.budget.history')->name('app.budgets.history');
        Volt::route('import', 'pages.budget.import-wizard')->name('app.budgets.import');

        Route::middleware(['can:create_budgets'])->group(function () {
            Volt::route('create', 'pages.budget.manual-create')->name('app.budgets.create');
        });

        Route::middleware(['can:edit_budgets'])->group(function () {
            Volt::route('edit/{budget}', 'pages.budget.manual-edit')->name('app.budgets.edit');
        });
    });

    // ========================================
    // MÓDULO HORAS
    // ========================================

    Route::prefix('hours')->middleware(['can:view_hours'])->group(function () {
        Volt::route('/', 'pages.hours.index')->name('app.hours.index');
        Volt::route('history', 'pages.hours.index')->name('app.hours.history');
        Volt::route('import', 'pages.hours.import-wizard')->name('app.hours.import');

        Route::middleware(['can:create_hours'])->group(function () {
            Volt::route('create', 'pages.hours.manual-create')->name('app.hours.create');
        });

        Route::middleware(['can:edit_hours'])->group(function () {
            Volt::route('edit/{hourDetail}', 'pages.hours.manual-edit')->name('app.hours.edit');
        });

        // Catálogo de personal
        Volt::route('catalogs/employees', 'pages.hours.catalogs.employees.index')->name('app.hours.catalogs.employees');
    });

    // ========================================
    // MÓDULO COMPRAS
    // ========================================

    Route::prefix('purchases')->middleware(['can:view_purchases'])->group(function () {
        Volt::route('/', 'pages.purchases.index')->name('app.purchases.index');
        Route::get('history', function () {
            $purchases = \App\Models\PurchaseDetail::latest()->take(50)->get();
            return view('historial-compras', ['purchases' => $purchases]);
        })->name('app.purchases.history');
        Volt::route('import', 'pages.purchases.import-wizard')->name('app.purchases.import');

        Route::middleware(['can:create_purchases'])->group(function () {
            Volt::route('create', 'pages.purchases.manual-create')->name('app.purchases.create');
        });

        Route::middleware(['can:edit_purchases'])->group(function () {
            Volt::route('edit/{purchaseDetail}', 'pages.purchases.manual-edit')->name('app.purchases.edit');
        });

        // Catálogo de proveedores
        Volt::route('catalogs/suppliers', 'pages.purchases.catalogs.suppliers.index')->name('app.purchases.catalogs.suppliers');
    });

    // ========================================
    // MÓDULO SATISFACCIÓN PERSONAL
    // ========================================

    Route::prefix('staff-satisfaction')->middleware(['can:view_staff_satisfaction'])->group(function () {
        Volt::route('/', 'pages.staff-satisfaction.survey-list')->name('app.staff-satisfaction.index');
        Volt::route('surveys', 'pages.staff-satisfaction.survey-list')->name('app.staff-satisfaction.surveys');
        Volt::route('import', 'pages.staff-satisfaction.import-wizard')->name('app.staff-satisfaction.import');

        Route::middleware(['can:create_staff_satisfaction'])->group(function () {
            Volt::route('create', 'pages.staff-satisfaction.manual-create')->name('app.staff-satisfaction.create');
        });
    });

    // ========================================
    // MÓDULO SATISFACCIÓN CLIENTES
    // ========================================

    Route::prefix('client-satisfaction')->middleware(['can:view_client_satisfaction'])->group(function () {
        Volt::route('/', 'pages.client-satisfaction.history')->name('app.client-satisfaction.index');
        Volt::route('history', 'pages.client-satisfaction.history')->name('app.client-satisfaction.history');
        Volt::route('import', 'pages.client-satisfaction.import-wizard')->name('app.client-satisfaction.import');

        Route::middleware(['can:create_client_satisfaction'])->group(function () {
            Volt::route('create', 'pages.client-satisfaction.manual-create')->name('app.client-satisfaction.create');
        });
    });

    // ========================================
    // MÓDULO TABLEROS
    // ========================================

    Route::prefix('boards')->middleware(['can:view_boards'])->group(function () {
        Volt::route('/', 'pages.boards.index')->name('app.boards.index');
        Route::get('history', function () {
            $boards = \App\Models\BoardDetail::latest()->take(50)->get();
            return view('historial-tableros', ['boards' => $boards]);
        })->name('app.boards.history');
        Volt::route('import', 'pages.boards.import-wizard')->name('app.boards.import');

        Route::middleware(['can:create_boards'])->group(function () {
            Volt::route('create', 'pages.boards.manual-create')->name('app.boards.create');
        });

        Route::middleware(['can:edit_boards'])->group(function () {
            Volt::route('edit/{boardDetail}', 'pages.boards.manual-edit')->name('app.boards.edit');
        });
    });

    // ========================================
    // MÓDULO AUTOMATIZACIÓN
    // ========================================

    Route::prefix('automation')->middleware(['can:view_automation'])->group(function () {
        Volt::route('/', 'pages.automation-projects.index')->name('app.automation.index');
        Route::get('history', function () {
            $projects = \App\Models\AutomationProject::latest()->take(50)->get();
            return view('historial-automation-projects', ['projects' => $projects]);
        })->name('app.automation.history');
        Volt::route('import', 'pages.automation-projects.import-wizard')->name('app.automation.import');

        Route::middleware(['can:create_automation'])->group(function () {
            Volt::route('create', 'pages.automation-projects.manual-create')->name('app.automation.create');
        });

        Route::middleware(['can:edit_automation'])->group(function () {
            Volt::route('edit/{automationProject}', 'pages.automation-projects.manual-edit')->name('app.automation.edit');
        });

        // Catálogo de proyectos
        Volt::route('catalogs/projects', 'pages.automation-projects.catalogs.projects.index')->name('app.automation.catalogs.projects');
    });

    // ========================================
    // MÓDULO PRODUCCIÓN
    // ========================================

    Route::prefix('production')->middleware(['can:view_production'])->group(function () {
        Volt::route('/', 'pages.manufacturing.production-log')->name('app.production.index');
    });

    // ========================================
    // MÓDULO RRHH
    // ========================================

    Route::prefix('hr')->middleware(['can:view_hr'])->group(function () {
        Volt::route('/', 'pages.hr.factor-manager')->name('app.hr.index');
        Volt::route('factors', 'pages.hr.factor-manager')->name('app.hr.factors');
    });

    // ========================================
    // GESTIÓN DE USUARIOS
    // ========================================

    Route::prefix('users')->middleware(['can:view_users'])->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('app.users.index');

        Route::middleware(['can:create_users'])->group(function () {
            Route::get('create', [UserController::class, 'create'])->name('app.users.create');
            Route::post('/', [UserController::class, 'store'])->name('app.users.store');
        });

        Route::middleware(['can:edit_users'])->group(function () {
            Route::get('{user}', [UserController::class, 'show'])->name('app.users.show');
            Route::get('{user}/edit', [UserController::class, 'edit'])->name('app.users.edit');
            Route::put('{user}', [UserController::class, 'update'])->name('app.users.update');
        });

        Route::middleware(['can:delete_users'])->group(function () {
            Route::delete('{user}', [UserController::class, 'destroy'])->name('app.users.destroy');
        });
    });

    // ========================================
    // GESTIÓN DE ROLES
    // ========================================

    Route::prefix('roles')->middleware(['can:view_roles'])->group(function () {
        Route::get('/', [RoleController::class, 'index'])->name('app.roles.index');
        Route::get('{role}/permissions', [RoleController::class, 'permissions'])->name('app.roles.permissions');

        Route::middleware(['can:create_roles'])->group(function () {
            Route::get('create', [RoleController::class, 'create'])->name('app.roles.create');
            Route::post('/', [RoleController::class, 'store'])->name('app.roles.store');
        });

        Route::middleware(['can:edit_roles'])->group(function () {
            Route::get('{role}', [RoleController::class, 'show'])->name('app.roles.show');
            Route::get('{role}/edit', [RoleController::class, 'edit'])->name('app.roles.edit');
            Route::put('{role}', [RoleController::class, 'update'])->name('app.roles.update');
            Route::post('{role}/permissions', [RoleController::class, 'updatePermissions'])->name('app.roles.permissions.update');
        });

        Route::middleware(['can:delete_roles'])->group(function () {
            Route::delete('{role}', [RoleController::class, 'destroy'])->name('app.roles.destroy');
        });
    });

    // ========================================
    // CATÁLOGOS GENERALES
    // ========================================

    Route::prefix('catalogs')->middleware(['can:view_dashboards'])->group(function () {
        Volt::route('cost-centers', 'pages.manager.catalogs.cost-centers.index')->name('app.catalogs.cost-centers');
        Volt::route('cost-centers/{id}', 'pages.manager.catalogs.cost-centers.show')->name('app.catalogs.cost-centers.show');
    });
});

// ============================================================================
// REDIRECCIONES DE COMPATIBILIDAD
// ============================================================================
// Estas redirecciones mantienen compatibilidad con URLs antiguas.
// Pueden eliminarse después de un período de transición.
// ============================================================================

Route::middleware(['auth'])->group(function () {
    // Dashboard redirects
    Route::redirect('dashboard', '/app/dashboard');
    Route::redirect('admin/dashboard', '/app/dashboard');
    Route::redirect('gerente/dashboard', '/app/dashboard');

    // Ventas redirects
    Route::redirect('admin/historial-ventas', '/app/sales/history');
    Route::redirect('gerente/historial-ventas', '/app/sales/history');
    Route::redirect('ventas/historial-ventas', '/app/sales/history');

    // Presupuestos redirects
    Route::redirect('admin/historial-presupuestos', '/app/budgets/history');
    Route::redirect('gerente/historial-presupuestos', '/app/budgets/history');
    Route::redirect('presupuesto/historial_importacion', '/app/budgets/history');

    // Horas redirects
    Route::redirect('admin/detalles-horas', '/app/hours');
    Route::redirect('gerente/detalles-horas', '/app/hours');
    Route::redirect('detalle_horas/historial_importacion', '/app/hours');

    // Compras redirects
    Route::redirect('admin/compras-materiales', '/app/purchases');
    Route::redirect('gerente/compras-materiales', '/app/purchases');
    Route::redirect('compras/historial_importacion', '/app/purchases/history');

    // Satisfacción redirects
    Route::redirect('admin/satisfaccion-personal', '/app/staff-satisfaction');
    Route::redirect('gerente/satisfaccion-personal', '/app/staff-satisfaction');
    Route::redirect('satisfaccion_personal/encuesta', '/app/staff-satisfaction/surveys');

    Route::redirect('admin/satisfaccion-clientes', '/app/client-satisfaction');
    Route::redirect('gerente/satisfaccion-clientes', '/app/client-satisfaction');
    Route::redirect('satisfaccion_clientes/historial_importacion', '/app/client-satisfaction/history');

    // Tableros redirects
    Route::redirect('admin/tableros', '/app/boards');
    Route::redirect('gerente/tableros', '/app/boards');
    Route::redirect('tableros/historial_importacion', '/app/boards/history');

    // Automatización redirects
    Route::redirect('admin/automatizacion', '/app/automation');
    Route::redirect('gerente/automatizacion', '/app/automation');
    Route::redirect('proyectos_automatizacion/historial_importacion', '/app/automation/history');

    // Usuarios y roles redirects
    Route::redirect('admin/users', '/app/users');
    Route::redirect('gerente/users', '/app/users');
    Route::redirect('admin/roles', '/app/roles');
    Route::redirect('gerente/roles', '/app/roles');

    // Otros redirects
    Route::redirect('admin/produccion', '/app/production');
    Route::redirect('gerente/produccion', '/app/production');
    Route::redirect('admin/rrhh', '/app/hr');
    Route::redirect('gerente/rrhh', '/app/hr');
    Route::redirect('admin/bitacora', '/app/audit');
    Route::redirect('gerente/bitacora', '/app/audit');
    Route::redirect('gerente/inteligencia', '/app/intelligence');
});

require __DIR__.'/auth.php';
