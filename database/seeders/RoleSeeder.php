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
        $superAdmin = Role::firstOrCreate(['name' => 'Super Admin']);
        $superAdmin->givePermissionTo(Permission::all());

        // Crear rol Admin con permisos de gestión (Programadores/Técnicos)
        $admin = Role::firstOrCreate(['name' => 'Admin']);
        $admin->givePermissionTo([
            'view_users', 'create_users', 'edit_users', 'delete_users',
            'view_roles', 'create_roles', 'edit_roles', 'delete_roles', // Gestión de Roles
            'view_sales', 'create_sales', 'edit_sales',
            'view_production', 'create_production', 'edit_production',
            'view_hr', 'create_hr', 'edit_hr',
            'view_dashboards',
            // Nuevos módulos
            'view_hours', 'create_hours', 'edit_hours',
            'view_purchases', 'create_purchases', 'edit_purchases',
            'view_staff_satisfaction', 'create_staff_satisfaction', 'edit_staff_satisfaction',
            'view_client_satisfaction', 'create_client_satisfaction', 'edit_client_satisfaction',
            'view_boards', 'create_boards', 'edit_boards',
            'view_automation', 'create_automation', 'edit_automation',
        ]);

        // Crear rol Manager con permisos operativos y administrativos (Gerente)
        $manager = Role::firstOrCreate(['name' => 'Manager']);
        $manager->givePermissionTo([
            'view_users', 'create_users', 'edit_users', 'delete_users', // Gestión de Usuarios completa
            'view_roles', 'create_roles', 'edit_roles', 'delete_roles', // Gestión de Roles completa
            'view_sales', 'create_sales', 'edit_sales',
            'view_production', 'create_production', 'edit_production',
            'view_hr',
            'view_dashboards',
            // Nuevos módulos (solo view para Manager)
            'view_hours',
            'view_purchases',
            'view_staff_satisfaction',
            'view_client_satisfaction',
            'view_boards',
            'view_automation',
        ]);


    }
}
