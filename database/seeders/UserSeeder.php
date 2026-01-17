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
                'email' => 'superadmin@gadium.com',
                'role' => 'Super Admin'
            ],
            [
                'name' => 'Administrador',
                'email' => 'admin@gadium.com',
                'role' => 'Admin'
            ],
            [
                'name' => 'Gerente',
                'email' => 'manager@gadium.com',
                'role' => 'Manager'
            ],
            [
                'name' => 'Vendedor',
                'email' => 'ventas@gadium.com',
                'role' => 'Vendedor'
            ],
            [
                'name' => 'Presupuestador',
                'email' => 'presupuesto@gadium.com',
                'role' => 'Presupuestador'
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
            
            $this->command->info("✅ Usuario {$userData['name']} ({$userData['email']}) actualizado con rol {$userData['role']}");
        }
    }
}
