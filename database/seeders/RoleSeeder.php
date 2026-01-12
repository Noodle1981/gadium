<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear rol Super Admin con todos los permisos
        $superAdmin = Role::create(['name' => 'Super Admin']);
        $superAdmin->givePermissionTo(Permission::all());

        // Crear rol Admin con permisos de gestión (Programadores/Técnicos)
        $admin = Role::create(['name' => 'Admin']);
        $admin->givePermissionTo([
            'view_users', 'create_users', 'edit_users', 'delete_users',
            'view_roles', 'create_roles', 'edit_roles', 'delete_roles', // Gestión de Roles
            'view_sales', 'create_sales', 'edit_sales',
            'view_production', 'create_production', 'edit_production',
            'view_hr', 'create_hr', 'edit_hr',
            'view_dashboards',
        ]);

        // Crear rol Manager con permisos operativos y administrativos (Gerente)
        $manager = Role::create(['name' => 'Manager']);
        $manager->givePermissionTo([
            'view_users', 'create_users', 'edit_users', 'delete_users', // Gestión de Usuarios completa
            'view_roles', 'create_roles', 'edit_roles', 'delete_roles', // Gestión de Roles completa
            'view_sales', 'create_sales', 'edit_sales',
            'view_production', 'create_production', 'edit_production',
            'view_hr',
            'view_dashboards',
        ]);
    }
}
