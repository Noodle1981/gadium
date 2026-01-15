<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class ClientSatisfactionModuleSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $permission = Permission::firstOrCreate(['name' => 'view_client_satisfaction']);
            $role = Role::firstOrCreate(['name' => 'Gestor de Satisfacción Clientes']);
            if (!$role->hasPermissionTo($permission)) $role->givePermissionTo($permission);

            $user = User::firstOrCreate(
                ['email' => 'satisfaccion_clientes@gadium.com'],
                ['name' => 'Usuario Satisfacción Clientes', 'password' => Hash::make('password'), 'email_verified_at' => now()]
            );
            if (!$user->hasRole('Gestor de Satisfacción Clientes')) $user->assignRole($role);
        });
    }
}
