<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class SalesUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear o obtener rol de Vendedor
        $role = Role::firstOrCreate(['name' => 'Vendedor']);
        
        // Asignar permisos de ventas al rol
        $role->givePermissionTo([
            'view_sales',
        ]);

        // Crear usuario de ventas
        $user = User::firstOrCreate(
            ['email' => 'ventas@gadium.com'],
            [
                'name' => 'Usuario Ventas',
                'password' => Hash::make('password'),
            ]
        );

        // Asignar rol
        $user->assignRole('Vendedor');

        $this->command->info('✅ Usuario de ventas creado: ventas@gadium.com / password');
    }
}
