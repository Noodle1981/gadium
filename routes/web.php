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
                Volt::route('historial-ventas', 'pages.sales.history')->name('admin.historial.ventas');
                Volt::route('ventas/editar/{sale}', 'pages.sales.manual-edit')->name('admin.sales.edit');
                
                Volt::route('historial-presupuestos', 'pages.budget.history')->name('admin.historial.presupuesto');
                Volt::route('presupuestos/importacion', 'pages.budget.import-wizard')->name('admin.budget.import');
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
                Volt::route('detalles-horas/importacion', 'pages.hours.import-wizard')->name('admin.hours.import');
                Volt::route('detalles-horas/crear', 'pages.hours.manual-create')->name('admin.hours.create');
                Volt::route('detalles-horas/editar/{hourDetail}', 'pages.hours.manual-edit')->name('admin.hours.edit');
                
                Volt::route('historial-horas', 'pages.hours.index')->name('admin.historial.horas');
            });

            // Módulo Compras Materiales
            Route::middleware(['can:view_purchases'])->group(function () {
                Volt::route('compras-materiales', 'pages.purchases.index')->name('admin.purchases.index');
                Volt::route('compras-materiales/importacion', 'pages.purchases.import-wizard')->name('admin.purchases.import');
                Volt::route('compras-materiales/crear', 'pages.purchases.manual-create')->name('admin.purchases.create');
                Volt::route('compras-materiales/editar/{purchaseDetail}', 'pages.purchases.manual-edit')->name('admin.purchases.edit');
                
                Route::get('historial-compras', function () {
                    $purchases = \App\Models\PurchaseDetail::latest()->take(50)->get();
                    return view('historial-compras', ['purchases' => $purchases]);
                })->name('admin.historial.compras');
            });

            // Módulo Tableros
            Route::middleware(['can:view_boards'])->group(function () {
                Volt::route('tableros', 'pages.boards.index')->name('admin.boards.index');
                Volt::route('tableros/importacion', 'pages.boards.import-wizard')->name('admin.boards.import');
                Volt::route('tableros/crear', 'pages.boards.manual-create')->name('admin.boards.create');
                Volt::route('tableros/editar/{boardDetail}', 'pages.boards.manual-edit')->name('admin.boards.edit');
                
                Route::get('historial-tableros', function () {
                    $boards = \App\Models\BoardDetail::latest()->take(50)->get();
                    return view('historial-tableros', ['boards' => $boards]);
                })->name('admin.historial.tableros');
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

            // Módulo Automatización
            Route::middleware(['can:view_automation'])->group(function () {
                Volt::route('automatizacion', 'pages.automation-projects.index')->name('admin.automation.index');
                Volt::route('automatizacion/importacion', 'pages.automation-projects.import-wizard')->name('admin.automation.import');
                
                Route::get('automatizacion/historial', function () {
                    $projects = \App\Models\AutomationProject::latest()->take(50)->get();
                    return view('historial-automation-projects', ['projects' => $projects]);
                })->name('admin.automation.historial');
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
            // Index uses Volt component (Visual Refactor)
            Volt::route('users', 'pages.manager.users.index')->name('manager.users.index');

            // CRUD Actions use Controller
            Route::resource('users', UserController::class)->except(['index'])->names([
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
            // Index uses Volt component (Visual Refactor)
            Volt::route('roles', 'pages.manager.roles.index')->name('manager.roles.index');

            // CRUD Actions use Controller
            Route::resource('roles', RoleController::class)->except(['index'])->names([
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
        Volt::route('historial-ventas', 'pages.sales.history')->name('manager.historial.ventas');
        Volt::route('ventas/editar/{sale}', 'pages.sales.manual-edit')->name('manager.sales.edit');
        
        Volt::route('historial-presupuestos', 'pages.budget.history')->name('manager.historial.presupuesto');
        Volt::route('presupuestos/importacion', 'pages.budget.import-wizard')->name('manager.budget.import');

        // Nuevos Módulos para Manager
        Volt::route('detalles-horas', 'pages.hours.index')->name('manager.hours.index');
        Volt::route('detalles-horas/importacion', 'pages.hours.import-wizard')->name('manager.hours.import');
        Volt::route('detalles-horas/crear', 'pages.hours.manual-create')->name('manager.hours.create');
        Volt::route('detalles-horas/editar/{hourDetail}', 'pages.hours.manual-edit')->name('manager.hours.edit');
        
        Volt::route('historial-horas', 'pages.hours.index')->name('manager.historial.horas');
        Volt::route('compras-materiales', 'pages.purchases.index')->name('manager.purchases.index');
        Volt::route('compras-materiales/importacion', 'pages.purchases.import-wizard')->name('manager.purchases.import');
        Volt::route('compras-materiales/crear', 'pages.purchases.manual-create')->name('manager.purchases.create');
        Volt::route('compras-materiales/editar/{purchaseDetail}', 'pages.purchases.manual-edit')->name('manager.purchases.edit');
        
        Route::get('historial-compras', function () {
            $purchases = \App\Models\PurchaseDetail::latest()->take(50)->get();
            return view('historial-compras', ['purchases' => $purchases]);
        })->name('manager.historial.compras');
        Volt::route('satisfaccion-personal', 'pages.staff-satisfaction.index')->name('manager.staff-satisfaction.index');
        Volt::route('satisfaccion-clientes', 'pages.client-satisfaction.index')->name('manager.client-satisfaction.index');
        Volt::route('tableros', 'pages.boards.index')->name('manager.boards.index');

        // Rutas de Tableros para Manager
        Route::middleware(['can:view_boards'])->group(function () {
             Volt::route('tableros-control', 'pages.boards.dashboard')->name('manager.boards.dashboard'); // Optional dashboard view
             // Manager uses same views but different route names if needed, or share?
             // Usually Manager has their own prefix. Let's redirect or use same components.
             // Following pattern:
             Volt::route('tableros/importacion', 'pages.boards.import-wizard')->name('manager.boards.import');
             Volt::route('tableros/crear', 'pages.boards.manual-create')->name('manager.boards.create');
             Volt::route('tableros/editar/{boardDetail}', 'pages.boards.manual-edit')->name('manager.boards.edit');
             
             Route::get('historial-tableros', function () {
                $boards = \App\Models\BoardDetail::latest()->take(50)->get();
                return view('historial-tableros', ['boards' => $boards]);
            })->name('manager.historial.tableros');
        });

        // Rutas de Automatización para Manager
        Route::middleware(['can:view_automation'])->group(function () {
            Volt::route('automatizacion', 'pages.automation-projects.index')->name('manager.automation.index');
            Volt::route('automatizacion/importacion', 'pages.automation-projects.import-wizard')->name('manager.automation.import');

            Route::get('automatizacion/historial', function () {
                $projects = \App\Models\AutomationProject::latest()->take(50)->get();
                return view('historial-automation-projects', ['projects' => $projects]);
            })->name('manager.automation.historial');
        });

        // --- GESTIÓN DE CATÁLOGOS MAESTROS ---
        Route::prefix('catalogo')->group(function () {
            // Proyectos
            Volt::route('proyectos', 'pages.manager.catalogs.projects.index')->name('manager.catalogs.projects.index');
            
            // Clientes
            Volt::route('clientes', 'pages.manager.catalogs.clients.index')->name('manager.catalogs.clients.index');
            
            // Personal & Alias
            Volt::route('personal', 'pages.manager.catalogs.employees.index')->name('manager.catalogs.employees.index');

            // Proveedores
            Volt::route('proveedores', 'pages.manager.catalogs.suppliers.index')->name('manager.catalogs.suppliers.index');

            // Centros de Costo
            Volt::route('centros-costo', 'pages.manager.catalogs.cost-centers.index')->name('manager.catalogs.cost-centers.index');
        });
    });


    // --- VENTAS (Vendedor) ---
    Route::prefix('ventas')->middleware(['role:Vendedor'])->group(function () {
        
        Volt::route('dashboard', 'pages.sales.dashboard')->name('sales.dashboard');
        Route::view('perfil', 'profile')->name('sales.profile');
        
        // Módulo Ventas
        Route::middleware(['can:view_sales'])->group(function () {
            Volt::route('importacion', 'pages.sales.import-wizard')->name('sales.import');
            Volt::route('crear', 'pages.sales.manual-create')->name('sales.create');
            Volt::route('editar/{sale}', 'pages.sales.manual-edit')->name('sales.edit');
            Volt::route('resolucion-clientes', 'pages.clients.resolution')->name('sales.clients.resolve');
            
            // Historial de Ventas
            Volt::route('historial-ventas', 'pages.sales.history')->name('sales.historial.ventas');
        });
    });


    // --- PRESUPUESTO (Presupuestador) ---
    Route::prefix('presupuesto')->middleware(['role:Presupuestador'])->group(function () {
        
        Volt::route('dashboard', 'pages.budget.dashboard')->name('budget.dashboard');
        Route::view('perfil', 'profile')->name('budget.profile');
        
        // Módulo Presupuesto
        Route::middleware(['can:view_budgets'])->group(function () {
            Volt::route('importacion', 'pages.budget.import-wizard')->name('budget.import');
            Volt::route('crear', 'pages.budget.manual-create')->name('budget.create');
            Volt::route('editar/{budget}', 'pages.budget.manual-edit')->name('budget.edit');
            
            Volt::route('historial_importacion', 'pages.budget.history')->name('budget.historial.importacion');
        });
    });


    // --- DETALLE DE HORAS (Gestor de Horas) ---
    Route::prefix('detalle_horas')->middleware(['role:Gestor de Horas'])->group(function () {
        Volt::route('dashboard', 'pages.hours.dashboard')->name('hours.dashboard');
        Route::view('perfil', 'profile')->name('hours.profile');
        
        Route::middleware(['can:view_hours'])->group(function () {
            Volt::route('importacion', 'pages.hours.import-wizard')->name('hours.import');
            Volt::route('crear', 'pages.hours.manual-create')->name('hours.create');
            Volt::route('editar/{hourDetail}', 'pages.hours.manual-edit')->name('hours.edit');
            
            Volt::route('historial_importacion', 'pages.hours.index')->name('hours.historial.importacion');
        });
    });

    // --- COMPRAS (Gestor de Compras) ---
    Route::prefix('compras')->middleware(['role:Gestor de Compras'])->group(function () {
        Volt::route('dashboard', 'pages.purchases.dashboard')->name('purchases.dashboard');
        Route::view('perfil', 'profile')->name('purchases.profile');
        
        Route::middleware(['can:view_purchases'])->group(function () {
            Volt::route('importacion', 'pages.purchases.import-wizard')->name('purchases.import');
            Volt::route('crear', 'pages.purchases.manual-create')->name('purchases.create');
            Volt::route('editar/{purchaseDetail}', 'pages.purchases.manual-edit')->name('purchases.edit');
            
            Route::get('historial_importacion', function () {
                $purchases = \App\Models\PurchaseDetail::latest()->take(50)->get();
                return view('historial-compras', ['purchases' => $purchases]);
            })->name('purchases.historial.importacion');
        });
    });

    // --- SATISFACCIÓN PERSONAL (Gestor de Satisfacción Personal) ---
    Route::prefix('satisfaccion_personal')->middleware(['role:Gestor de Satisfacción Personal'])->group(function () {
        Volt::route('dashboard', 'pages.staff-satisfaction.dashboard')->name('staff-satisfaction.dashboard');
        Route::view('perfil', 'profile')->name('staff-satisfaction.profile');

        Route::middleware(['can:view_staff_satisfaction'])->group(function () {
            Volt::route('importacion', 'pages.staff-satisfaction.import-wizard')->name('staff-satisfaction.import');
            Volt::route('crear', 'pages.staff-satisfaction.manual-create')->name('staff-satisfaction.create');
            Volt::route('historial_importacion', 'pages.staff-satisfaction.history')->name('staff-satisfaction.historial.importacion');
        });
    });

    // --- SATISFACCIÓN CLIENTES (Gestor de Satisfacción Clientes) ---
    Route::prefix('satisfaccion_clientes')->middleware(['role:Gestor de Satisfacción Clientes'])->group(function () {
    Volt::route('dashboard', 'pages.client-satisfaction.dashboard')->name('client-satisfaction.dashboard');
    Route::view('perfil', 'profile')->name('client-satisfaction.profile');

    Route::middleware(['can:view_client_satisfaction'])->group(function () {
        Volt::route('importacion', 'pages.client-satisfaction.import-wizard')->name('client-satisfaction.import');
        Volt::route('crear', 'pages.client-satisfaction.manual-create')->name('client-satisfaction.create');
        // Volt::route('editar/{clientSatisfactionResponse}', 'pages.client-satisfaction.manual-edit')->name('client-satisfaction.edit'); // Future
        
        Volt::route('historial_importacion', 'pages.client-satisfaction.history')->name('client-satisfaction.historial.importacion');
    });
});



    // --- PROYECTOS DE AUTOMATIZACIÓN (Gestor de Proyectos) ---
    Route::prefix('proyectos_automatizacion')->middleware(['role:Gestor de Proyectos'])->group(function () {
        Volt::route('dashboard', 'pages.automation-projects.dashboard')->name('automation_projects.dashboard');
        Route::view('perfil', 'profile')->name('automation_projects.profile');
        
        Route::middleware(['can:view_automation'])->group(function () {
            Volt::route('importacion', 'pages.automation-projects.import-wizard')->name('automation_projects.import');
            Volt::route('crear', 'pages.automation-projects.manual-create')->name('automation_projects.create');
            Volt::route('editar/{automationProject}', 'pages.automation-projects.manual-edit')->name('automation_projects.edit');
            
            Route::get('historial_importacion', function () {
                $projects = \App\Models\AutomationProject::latest()->take(50)->get();
                return view('historial-automation-projects', ['projects' => $projects]);
            })->name('automation_projects.historial.importacion');
        });
    });



    // --- MÓDULOS DE SISTEMA (Rutas Amigables) - ELIMINADOS PARA USAR RUTAS POR ROL
    // Las rutas genéricas han sido reemplazadas por rutas específicas dentro de los grupos Admin y Gerente.

});

// Grupo para Gestor de Tableros
Route::prefix('tableros')->middleware(['role:Gestor de Tableros'])->group(function () {
    Volt::route('dashboard', 'pages.boards.dashboard')->name('boards.dashboard');
    Route::view('perfil', 'profile')->name('boards.profile');
    
    Route::middleware(['can:view_boards'])->group(function () {
        Volt::route('importacion', 'pages.boards.import-wizard')->name('boards.import');
        Volt::route('crear', 'pages.boards.manual-create')->name('boards.create');
        Volt::route('editar/{boardDetail}', 'pages.boards.manual-edit')->name('boards.edit');
        
        Route::get('historial_importacion', function () {
            $boards = \App\Models\BoardDetail::latest()->take(50)->get();
            return view('historial-tableros', ['boards' => $boards]);
        })->name('boards.historial.importacion');
    });
});

require __DIR__.'/auth.php';
