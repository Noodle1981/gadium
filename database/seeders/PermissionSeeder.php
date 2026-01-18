<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Resetear cache de permisos
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Permisos de Usuarios
        Permission::firstOrCreate(['name' => 'view_users']);
        Permission::firstOrCreate(['name' => 'create_users']);
        Permission::firstOrCreate(['name' => 'edit_users']);
        Permission::firstOrCreate(['name' => 'delete_users']);

        // Permisos de Roles
        Permission::firstOrCreate(['name' => 'view_roles']);
        Permission::firstOrCreate(['name' => 'create_roles']);
        Permission::firstOrCreate(['name' => 'edit_roles']);
        Permission::firstOrCreate(['name' => 'delete_roles']);

        // Permisos de Ventas
        Permission::firstOrCreate(['name' => 'view_sales']);
        Permission::firstOrCreate(['name' => 'create_sales']);
        Permission::firstOrCreate(['name' => 'edit_sales']);
        Permission::firstOrCreate(['name' => 'delete_sales']);

        // Permisos de Producción
        Permission::firstOrCreate(['name' => 'view_production']);
        Permission::firstOrCreate(['name' => 'create_production']);
        Permission::firstOrCreate(['name' => 'edit_production']);
        Permission::firstOrCreate(['name' => 'delete_production']);

        // Permisos de RRHH
        Permission::firstOrCreate(['name' => 'view_hr']);
        Permission::firstOrCreate(['name' => 'create_hr']);
        Permission::firstOrCreate(['name' => 'edit_hr']);
        Permission::firstOrCreate(['name' => 'delete_hr']);

        // Permisos de Dashboards
        Permission::firstOrCreate(['name' => 'view_dashboards']);
        Permission::firstOrCreate(['name' => 'manage_dashboards']);

        // Permisos de Detalles Horas
        Permission::firstOrCreate(['name' => 'view_hours']);
        Permission::firstOrCreate(['name' => 'create_hours']);
        Permission::firstOrCreate(['name' => 'edit_hours']);
        Permission::firstOrCreate(['name' => 'delete_hours']);

        // Permisos de Compras Materiales
        Permission::firstOrCreate(['name' => 'view_purchases']);
        Permission::firstOrCreate(['name' => 'create_purchases']);
        Permission::firstOrCreate(['name' => 'edit_purchases']);
        Permission::firstOrCreate(['name' => 'delete_purchases']);

        // Permisos de Satisfacción Personal
        Permission::firstOrCreate(['name' => 'view_staff_satisfaction']);
        Permission::firstOrCreate(['name' => 'create_staff_satisfaction']);
        Permission::firstOrCreate(['name' => 'edit_staff_satisfaction']);
        Permission::firstOrCreate(['name' => 'delete_staff_satisfaction']);

        // Permisos de Satisfacción Clientes
        Permission::firstOrCreate(['name' => 'view_client_satisfaction']);
        Permission::firstOrCreate(['name' => 'create_client_satisfaction']);
        Permission::firstOrCreate(['name' => 'edit_client_satisfaction']);
        Permission::firstOrCreate(['name' => 'delete_client_satisfaction']);

        // Permisos de Tableros
        Permission::firstOrCreate(['name' => 'view_boards']);
        Permission::firstOrCreate(['name' => 'create_boards']);
        Permission::firstOrCreate(['name' => 'edit_boards']);
        Permission::firstOrCreate(['name' => 'delete_boards']);

        // Permisos de Proyecto Automatización
        Permission::firstOrCreate(['name' => 'view_automation']);
        Permission::firstOrCreate(['name' => 'create_automation']);
        Permission::firstOrCreate(['name' => 'edit_automation']);
        Permission::firstOrCreate(['name' => 'delete_automation']);
    }
}
