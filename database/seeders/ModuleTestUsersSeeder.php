<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class ModuleTestUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Crea usuarios de prueba para cada módulo nuevo con permisos específicos.
     */
    public function run(): void
    {
        echo "\n🔧 Creando usuarios de prueba para módulos nuevos...\n";

        // Definición basada en doc/credenciales.md
        $testUsers = [
            [
                'name' => 'Gestor de Horas',
                'email' => 'horas@gadium.com',
                'role' => 'Gestor de Horas',
                'permissions' => ['view_hours', 'create_hours', 'edit_hours'],
            ],
            [
                'name' => 'Gestor de Compras',
                'email' => 'compras@gadium.com',
                'role' => 'Gestor de Compras',
                'permissions' => ['view_purchases', 'create_purchases', 'edit_purchases'],
            ],
            [
                'name' => 'Gestor de Satisfacción Personal',
                'email' => 'satisfaccion_personal@gadium.com',
                'role' => 'Gestor de Satisfacción Personal',
                'permissions' => ['view_staff_satisfaction', 'create_staff_satisfaction', 'edit_staff_satisfaction'],
            ],
            [
                'name' => 'Gestor de Satisfacción Clientes',
                'email' => 'satisfaccion_clientes@gadium.com',
                'role' => 'Gestor de Satisfacción Clientes',
                'permissions' => ['view_client_satisfaction', 'create_client_satisfaction', 'edit_client_satisfaction'],
            ],
            [
                'name' => 'Gestor de Tableros',
                'email' => 'tableros@gadium.com',
                'role' => 'Gestor de Tableros',
                'permissions' => ['view_boards', 'create_boards', 'edit_boards'],
            ],
            [
                'name' => 'Gestor de Proyectos',
                'email' => 'proyectos@gadium.com',
                'role' => 'Gestor de Proyectos',
                'permissions' => ['view_automation', 'create_automation', 'edit_automation'],
            ],
        ];

        foreach ($testUsers as $userData) {
            
            // Garantizar que el rol existe antes de asignarlo
            $role = \Spatie\Permission\Models\Role::firstOrCreate(['name' => $userData['role']]);
            
            // Crear o Actualizar usuario (reseteando password)
            $user = User::updateOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ]
            );

            // Asignar rol
            $user->syncRoles([$userData['role']]);

            // Asignar permisos (opcional, si el rol ya los tiene no haría falta, pero por seguridad)
            // Primero aseguramos que los permisos existan
            foreach ($userData['permissions'] as $perm) {
                \Spatie\Permission\Models\Permission::firstOrCreate(['name' => $perm]);
            }
            $user->syncPermissions($userData['permissions']);

            echo "   ✅ {$userData['name']} ({$userData['email']}) - Rol: {$userData['role']} - Password: password\n";
        }

        echo "✅ Usuarios de prueba actualizados exitosamente.\n\n";
    }
}
