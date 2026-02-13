<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class SetupController extends Controller
{
    public function seed(): JsonResponse
    {
        // Resetear cache de permisos
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // ========================================
        // CREAR PERMISOS
        // ========================================
        $permissions = [
            'view_users', 'create_users', 'edit_users', 'delete_users',
            'view_roles', 'create_roles', 'edit_roles', 'delete_roles',
            'view_sales', 'create_sales', 'edit_sales', 'delete_sales',
            'view_production', 'create_production', 'edit_production', 'delete_production',
            'view_budgets', 'create_budgets', 'edit_budgets', 'delete_budgets',
            'view_hr', 'create_hr', 'edit_hr', 'delete_hr',
            'view_dashboards', 'manage_dashboards',
            'view_hours', 'create_hours', 'edit_hours', 'delete_hours',
            'view_purchases', 'create_purchases', 'edit_purchases', 'delete_purchases',
            'view_staff_satisfaction', 'create_staff_satisfaction', 'edit_staff_satisfaction', 'delete_staff_satisfaction',
            'view_client_satisfaction', 'create_client_satisfaction', 'edit_client_satisfaction', 'delete_client_satisfaction',
            'view_boards', 'create_boards', 'edit_boards', 'delete_boards',
            'view_automation', 'create_automation', 'edit_automation', 'delete_automation',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // ========================================
        // CREAR ROLES Y ASIGNAR PERMISOS
        // ========================================

        // Super Admin - todos los permisos
        $superAdmin = Role::firstOrCreate(['name' => 'Super Admin']);
        $superAdmin->givePermissionTo(Permission::all());

        // Admin
        $admin = Role::firstOrCreate(['name' => 'Admin']);
        $admin->syncPermissions([
            'view_users', 'create_users', 'edit_users', 'delete_users',
            'view_roles', 'create_roles', 'edit_roles', 'delete_roles',
            'view_sales', 'create_sales', 'edit_sales',
            'view_budgets', 'create_budgets', 'edit_budgets',
            'view_production', 'create_production', 'edit_production',
            'view_hr', 'create_hr', 'edit_hr',
            'view_dashboards',
            'view_hours', 'create_hours', 'edit_hours',
            'view_purchases', 'create_purchases', 'edit_purchases',
            'view_staff_satisfaction', 'create_staff_satisfaction', 'edit_staff_satisfaction',
            'view_client_satisfaction', 'create_client_satisfaction', 'edit_client_satisfaction',
            'view_boards', 'create_boards', 'edit_boards',
            'view_automation', 'create_automation', 'edit_automation',
        ]);

        // Manager
        $manager = Role::firstOrCreate(['name' => 'Manager']);
        $manager->syncPermissions([
            'view_users', 'create_users', 'edit_users', 'delete_users',
            'view_roles', 'create_roles', 'edit_roles', 'delete_roles',
            'view_sales', 'create_sales', 'edit_sales',
            'view_budgets', 'create_budgets', 'edit_budgets',
            'view_production', 'create_production', 'edit_production',
            'view_hr',
            'view_dashboards',
            'view_hours',
            'view_purchases',
            'view_staff_satisfaction',
            'view_client_satisfaction',
            'view_boards',
            'view_automation',
        ]);

        // ========================================
        // ROLES PERSONALIZADOS POR MÓDULO
        // ========================================

        // Ventas
        $ventas = Role::firstOrCreate(['name' => 'Ventas']);
        $ventas->syncPermissions([
            'view_sales', 'create_sales', 'edit_sales',
        ]);

        // Presupuestos
        $presupuestos = Role::firstOrCreate(['name' => 'Presupuestos']);
        $presupuestos->syncPermissions([
            'view_budgets', 'create_budgets', 'edit_budgets',
        ]);

        // Horas
        $horas = Role::firstOrCreate(['name' => 'Horas']);
        $horas->syncPermissions([
            'view_hours', 'create_hours', 'edit_hours',
        ]);

        // Compras
        $compras = Role::firstOrCreate(['name' => 'Compras']);
        $compras->syncPermissions([
            'view_purchases', 'create_purchases', 'edit_purchases',
        ]);

        // Tableros
        $tableros = Role::firstOrCreate(['name' => 'Tableros']);
        $tableros->syncPermissions([
            'view_boards', 'create_boards', 'edit_boards',
        ]);

        // Automatizacion
        $automatizacion = Role::firstOrCreate(['name' => 'Automatizacion']);
        $automatizacion->syncPermissions([
            'view_automation', 'create_automation', 'edit_automation',
        ]);

        // Satisfaccion Personal
        $satisfaccionPersonal = Role::firstOrCreate(['name' => 'Satisfaccion Personal']);
        $satisfaccionPersonal->syncPermissions([
            'view_staff_satisfaction', 'create_staff_satisfaction', 'edit_staff_satisfaction',
        ]);

        // Satisfaccion Clientes
        $satisfaccionClientes = Role::firstOrCreate(['name' => 'Satisfaccion Clientes']);
        $satisfaccionClientes->syncPermissions([
            'view_client_satisfaction', 'create_client_satisfaction', 'edit_client_satisfaction',
        ]);

        // Produccion
        $produccion = Role::firstOrCreate(['name' => 'Produccion']);
        $produccion->syncPermissions([
            'view_production', 'create_production', 'edit_production',
        ]);

        // RRHH
        $rrhh = Role::firstOrCreate(['name' => 'RRHH']);
        $rrhh->syncPermissions([
            'view_hr', 'create_hr', 'edit_hr',
        ]);

        // ========================================
        // CREAR USUARIOS
        // ========================================

        // Super Admin user
        $superAdminUser = User::firstOrCreate(
            ['email' => 'superadmin@gaudium.com'],
            [
                'name' => 'Super Administrador',
                'password' => Hash::make('password'),
            ]
        );
        $superAdminUser->syncRoles(['Super Admin']);

        // Admin user
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@gaudium.com'],
            [
                'name' => 'Administrador',
                'password' => Hash::make('password'),
            ]
        );
        $adminUser->syncRoles(['Admin']);

        // Resetear cache
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        return response()->json([
            'success' => true,
            'message' => 'Setup completado exitosamente.',
            'data' => [
                'permissions_count' => Permission::count(),
                'roles' => Role::pluck('name'),
                'users' => [
                    ['email' => $superAdminUser->email, 'role' => 'Super Admin'],
                    ['email' => $adminUser->email, 'role' => 'Admin'],
                ],
            ],
        ]);
    }
}
