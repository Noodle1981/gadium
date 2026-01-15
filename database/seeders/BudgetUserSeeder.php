<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class BudgetUserSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            // 1. Crear Permiso view_budgets si no existe
            $permission = Permission::firstOrCreate(['name' => 'view_budgets']);

            // 2. Crear Rol Presupuestador si no existe
            $role = Role::firstOrCreate(['name' => 'Presupuestador']);
            
            // 3. Asignar permiso al rol si no lo tiene
            if (!$role->hasPermissionTo($permission)) {
                $role->givePermissionTo($permission);
            }

            // 4. Crear Usuario Presupuesto
            $user = User::firstOrCreate(
                ['email' => 'presupuesto@gadium.com'],
                [
                    'name' => 'Usuario Presupuestos',
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ]
            );

            // 5. Asignar Rol
            if (!$user->hasRole('Presupuestador')) {
                $user->assignRole($role);
            }

            $this->command->info('Usuario Presupuesto creado: presupuesto@gadium.com / password');
        });
    }
}
