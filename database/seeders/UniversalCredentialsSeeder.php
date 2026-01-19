<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UniversalCredentialsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * SEEDER UNIVERSAL DE CREDENCIALES
     * Genera: Permisos -> Roles -> Usuarios (Base + Módulos)
     */
    public function run(): void
    {
        $this->command->info('🔐 Ejecutando UniversalCredentialsSeeder...');

        // ---------------------------------------------------------
        // 1. LIMPIEZA Y PREPARACIÓN
        // ---------------------------------------------------------
        // Resetear cache de permisos
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // ---------------------------------------------------------
        // 2. DEFINICIÓN Y CREACIÓN DE PERMISOS
        // ---------------------------------------------------------
        $this->command->warn('   → Generando Permisos...');
        
        $permissions = [
            // Usuarios
            'view_users', 'create_users', 'edit_users', 'delete_users',
            // Roles
            'view_roles', 'create_roles', 'edit_roles', 'delete_roles',
            // Ventas
            'view_sales', 'create_sales', 'edit_sales', 'delete_sales',
            // Producción
            'view_production', 'create_production', 'edit_production', 'delete_production',
            // RRHH
            'view_hr', 'create_hr', 'edit_hr', 'delete_hr',
            // Dashboards
            'view_dashboards', 'manage_dashboards',
            // Detalles Horas
            'view_hours', 'create_hours', 'edit_hours', 'delete_hours',
            // Compras
            'view_purchases', 'create_purchases', 'edit_purchases', 'delete_purchases',
            // Satisfacción Personal
            'view_staff_satisfaction', 'create_staff_satisfaction', 'edit_staff_satisfaction', 'delete_staff_satisfaction',
            // Satisfacción Clientes
            'view_client_satisfaction', 'create_client_satisfaction', 'edit_client_satisfaction', 'delete_client_satisfaction',
            // Tableros
            'view_boards', 'create_boards', 'edit_boards', 'delete_boards',
            // Automatización
            'view_automation', 'create_automation', 'edit_automation', 'delete_automation',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }
        
        // ---------------------------------------------------------
        // 3. DEFINICIÓN Y CREACIÓN DE ROLES
        // ---------------------------------------------------------
        $this->command->warn('   → Configurando Roles y asignando Permisos...');

        // 3.1 Super Admin (Acceso Total)
        $superAdmin = Role::firstOrCreate(['name' => 'Super Admin']);
        $superAdmin->givePermissionTo(Permission::all());

        // 3.2 Admin (Gestión Operativa)
        $admin = Role::firstOrCreate(['name' => 'Admin']);
        $admin->givePermissionTo([
            'view_users', 'create_users', 'edit_users', 'delete_users',
            'view_roles', 'create_roles', 'edit_roles', 'delete_roles',
            'view_sales', 'create_sales', 'edit_sales',
            'view_production', 'create_production', 'edit_production',
            'view_hr', 'create_hr', 'edit_hr',
            'view_dashboards',
            // Todos los módulos nuevos
            'view_hours', 'create_hours', 'edit_hours',
            'view_purchases', 'create_purchases', 'edit_purchases',
            'view_staff_satisfaction', 'create_staff_satisfaction', 'edit_staff_satisfaction',
            'view_client_satisfaction', 'create_client_satisfaction', 'edit_client_satisfaction',
            'view_boards', 'create_boards', 'edit_boards',
            'view_automation', 'create_automation', 'edit_automation',
        ]);

        // 3.3 Manager (Gerente)
        $manager = Role::firstOrCreate(['name' => 'Manager']);
        $manager->givePermissionTo([
            'view_users', 'create_users', 'edit_users', 'delete_users',
            'view_roles', 'create_roles', 'edit_roles', 'delete_roles',
            'view_sales', 'create_sales', 'edit_sales',
            'view_production', 'create_production', 'edit_production',
            'view_hr',
            'view_dashboards',
            // Solo lectura en módulos técnicos
            'view_hours',
            'view_purchases',
            'view_staff_satisfaction',
            'view_client_satisfaction',
            'view_boards',
            'view_automation',
        ]);

        // 3.4 Roles Específicos (Se asignarán a usuarios específicos)
        $rolesEspecificos = [
            'Vendedor', 'Presupuestador', 
            'Gestor de Horas', 'Gestor de Compras', 
            'Gestor de Satisfacción Personal', 'Gestor de Satisfacción Clientes',
            'Gestor de Tableros', 'Gestor de Proyectos'
        ];
        
        foreach ($rolesEspecificos as $roleName) {
            Role::firstOrCreate(['name' => $roleName]);
        }

        // ---------------------------------------------------------
        // 4. CREACIÓN DE USUARIOS
        // ---------------------------------------------------------
        $this->command->warn('   → Generando Usuarios...');

        $users = [
            // --- Usuarios Base ---
            [
                'name' => 'Super Administrador',
                'email' => 'superadmin@gaudium.com',
                'role' => 'Super Admin',
                'permissions_extra' => []
            ],
            [
                'name' => 'Administrador',
                'email' => 'admin@gaudium.com',
                'role' => 'Admin',
                'permissions_extra' => []
            ],
            [
                'name' => 'Gerente',
                'email' => 'manager@gaudium.com',
                'role' => 'Manager',
                'permissions_extra' => []
            ],
            [
                'name' => 'Vendedor',
                'email' => 'ventas@gaudium.com',
                'role' => 'Vendedor',
                'permissions_extra' => ['view_sales', 'create_sales', 'edit_sales']
            ],
            [
                'name' => 'Presupuestador',
                'email' => 'presupuesto@gaudium.com',
                'role' => 'Presupuestador',
                'permissions_extra' => ['view_budgets', 'create_budgets', 'edit_budgets']
            ],
            
            // --- Usuarios de Módulos (Gestores) ---
            [
                'name' => 'Gestor de Horas',
                'email' => 'horas@gaudium.com',
                'role' => 'Gestor de Horas',
                'permissions_extra' => ['view_hours', 'create_hours', 'edit_hours']
            ],
            [
                'name' => 'Gestor de Compras',
                'email' => 'compras@gaudium.com',
                'role' => 'Gestor de Compras',
                'permissions_extra' => ['view_purchases', 'create_purchases', 'edit_purchases']
            ],
            [
                'name' => 'Gestor de Satisfacción Personal',
                'email' => 'satisfaccion_personal@gaudium.com',
                'role' => 'Gestor de Satisfacción Personal',
                'permissions_extra' => ['view_staff_satisfaction', 'create_staff_satisfaction', 'edit_staff_satisfaction']
            ],
            [
                'name' => 'Gestor de Satisfacción Clientes',
                'email' => 'satisfaccion_clientes@gaudium.com',
                'role' => 'Gestor de Satisfacción Clientes',
                'permissions_extra' => ['view_client_satisfaction', 'create_client_satisfaction', 'edit_client_satisfaction']
            ],
            [
                'name' => 'Gestor de Tableros',
                'email' => 'tableros@gaudium.com',
                'role' => 'Gestor de Tableros',
                'permissions_extra' => ['view_boards', 'create_boards', 'edit_boards']
            ],
            [
                'name' => 'Gestor de Proyectos',
                'email' => 'proyectos@gaudium.com',
                'role' => 'Gestor de Proyectos',
                'permissions_extra' => ['view_automation', 'create_automation', 'edit_automation']
            ],
        ];

        foreach ($users as $userData) {
            // Asegurar que permisos extra existan (si fueran nuevos)
            foreach ($userData['permissions_extra'] as $perm) {
                Permission::firstOrCreate(['name' => $perm]);
            }

            // Crear Usuario
            $user = User::updateOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ]
            );

            // Asignar Rol
            $user->syncRoles([$userData['role']]);

            // Asignar Permisos Directos
            if (!empty($userData['permissions_extra'])) {
                $user->syncPermissions($userData['permissions_extra']);
            }
            
            $this->command->info("      + Usuario creado: {$userData['email']} ({$userData['role']})");
        }

        $this->command->info('✅ UniversalCredentialsSeeder completado exitosamente.');
    }
}
