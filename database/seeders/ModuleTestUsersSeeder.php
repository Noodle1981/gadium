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

        $testUsers = [
            [
                'name' => 'Test Detalles Horas',
                'email' => 'detalleshoras@gadium.com',
                'role' => 'Admin',
                'permissions' => ['view_hours', 'create_hours', 'edit_hours'],
            ],
            [
                'name' => 'Test Compras Materiales',
                'email' => 'comprasmateriales@gadium.com',
                'role' => 'Admin',
                'permissions' => ['view_purchases', 'create_purchases', 'edit_purchases'],
            ],
            [
                'name' => 'Test Satisfacción Personal',
                'email' => 'satisfaccionpersonal@gadium.com',
                'role' => 'Admin',
                'permissions' => ['view_staff_satisfaction', 'create_staff_satisfaction', 'edit_staff_satisfaction'],
            ],
            [
                'name' => 'Test Satisfacción Clientes',
                'email' => 'satisfaccionclientes@gadium.com',
                'role' => 'Admin',
                'permissions' => ['view_client_satisfaction', 'create_client_satisfaction', 'edit_client_satisfaction'],
            ],
            [
                'name' => 'Test Tableros',
                'email' => 'tableros@gadium.com',
                'role' => 'Admin',
                'permissions' => ['view_boards', 'create_boards', 'edit_boards'],
            ],
            [
                'name' => 'Test Automatización',
                'email' => 'automatizacion@gadium.com',
                'role' => 'Admin',
                'permissions' => ['view_automation', 'create_automation', 'edit_automation'],
            ],
        ];

        foreach ($testUsers as $userData) {
            $user = User::create([
                'name' => $userData['name'],
                'email' => $userData['email'],
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]);

            // Asignar rol
            $user->assignRole($userData['role']);

            // Asignar permisos específicos (además de los que ya tiene por el rol)
            $user->givePermissionTo($userData['permissions']);

            echo "   ✅ {$userData['name']} ({$userData['email']}) - Rol: {$userData['role']}\n";
        }

        echo "✅ Usuarios de prueba creados exitosamente.\n\n";
    }
}
