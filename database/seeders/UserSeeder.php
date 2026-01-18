<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Super Administrador',
                'email' => 'superadmin@gaudium.com',
                'role' => 'Super Admin',
                'permissions' => []
            ],
            [
                'name' => 'Administrador',
                'email' => 'admin@gaudium.com',
                'role' => 'Admin',
                'permissions' => []
            ],
            [
                'name' => 'Gerente',
                'email' => 'manager@gaudium.com',
                'role' => 'Manager',
                'permissions' => []
            ],
            [
                'name' => 'Vendedor',
                'email' => 'ventas@gaudium.com',
                'role' => 'Vendedor',
                'permissions' => ['view_sales', 'create_sales', 'edit_sales']
            ],
            [
                'name' => 'Presupuestador',
                'email' => 'presupuesto@gaudium.com',
                'role' => 'Presupuestador',
                'permissions' => ['view_budgets', 'create_budgets', 'edit_budgets']
            ],
        ];

        foreach ($users as $userData) {
            // Garantizar que el rol existe
            \Spatie\Permission\Models\Role::firstOrCreate(['name' => $userData['role']]);

            // Crear o actualizar usuario
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
            
            // Asignar permisos si existen
            if (!empty($userData['permissions'])) {
                foreach ($userData['permissions'] as $perm) {
                    \Spatie\Permission\Models\Permission::firstOrCreate(['name' => $perm]);
                }
                $user->syncPermissions($userData['permissions']);
            }
            
            $this->command->info("✅ Usuario {$userData['name']} ({$userData['email']}) actualizado con rol {$userData['role']}");
        }
    }
}
