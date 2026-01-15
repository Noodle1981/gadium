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
        Permission::create(['name' => 'view_users']);
        Permission::create(['name' => 'create_users']);
        Permission::create(['name' => 'edit_users']);
        Permission::create(['name' => 'delete_users']);

        // Permisos de Roles
        Permission::create(['name' => 'view_roles']);
        Permission::create(['name' => 'create_roles']);
        Permission::create(['name' => 'edit_roles']);
        Permission::create(['name' => 'delete_roles']);

        // Permisos de Ventas
        Permission::create(['name' => 'view_sales']);
        Permission::create(['name' => 'create_sales']);
        Permission::create(['name' => 'edit_sales']);
        Permission::create(['name' => 'delete_sales']);

        // Permisos de Producción
        Permission::create(['name' => 'view_production']);
        Permission::create(['name' => 'create_production']);
        Permission::create(['name' => 'edit_production']);
        Permission::create(['name' => 'delete_production']);

        // Permisos de RRHH
        Permission::create(['name' => 'view_hr']);
        Permission::create(['name' => 'create_hr']);
        Permission::create(['name' => 'edit_hr']);
        Permission::create(['name' => 'delete_hr']);

        // Permisos de Dashboards
        Permission::create(['name' => 'view_dashboards']);
        Permission::create(['name' => 'manage_dashboards']);

        // Permisos de Detalles Horas
        Permission::create(['name' => 'view_hours']);
        Permission::create(['name' => 'create_hours']);
        Permission::create(['name' => 'edit_hours']);
        Permission::create(['name' => 'delete_hours']);

        // Permisos de Compras Materiales
        Permission::create(['name' => 'view_purchases']);
        Permission::create(['name' => 'create_purchases']);
        Permission::create(['name' => 'edit_purchases']);
        Permission::create(['name' => 'delete_purchases']);

        // Permisos de Satisfacción Personal
        Permission::create(['name' => 'view_staff_satisfaction']);
        Permission::create(['name' => 'create_staff_satisfaction']);
        Permission::create(['name' => 'edit_staff_satisfaction']);
        Permission::create(['name' => 'delete_staff_satisfaction']);

        // Permisos de Satisfacción Clientes
        Permission::create(['name' => 'view_client_satisfaction']);
        Permission::create(['name' => 'create_client_satisfaction']);
        Permission::create(['name' => 'edit_client_satisfaction']);
        Permission::create(['name' => 'delete_client_satisfaction']);

        // Permisos de Tableros
        Permission::create(['name' => 'view_boards']);
        Permission::create(['name' => 'create_boards']);
        Permission::create(['name' => 'edit_boards']);
        Permission::create(['name' => 'delete_boards']);

        // Permisos de Proyecto Automatización
        Permission::create(['name' => 'view_automation']);
        Permission::create(['name' => 'create_automation']);
        Permission::create(['name' => 'edit_automation']);
        Permission::create(['name' => 'delete_automation']);
    }
}
