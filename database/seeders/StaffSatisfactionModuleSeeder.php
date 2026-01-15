<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class StaffSatisfactionModuleSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $permission = Permission::firstOrCreate(['name' => 'view_staff_satisfaction']);
            $role = Role::firstOrCreate(['name' => 'Gestor de Satisfacción Personal']);
            if (!$role->hasPermissionTo($permission)) $role->givePermissionTo($permission);

            $user = User::firstOrCreate(
                ['email' => 'satisfaccion_personal@gadium.com'],
                ['name' => 'Usuario Satisfacción Personal', 'password' => Hash::make('password'), 'email_verified_at' => now()]
            );
            if (!$user->hasRole('Gestor de Satisfacción Personal')) $user->assignRole($role);
        });
    }
}
