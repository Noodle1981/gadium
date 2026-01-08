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
    }
}
