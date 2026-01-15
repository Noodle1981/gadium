<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AutomationModuleSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $permission = Permission::firstOrCreate(['name' => 'view_automation']);
            $role = Role::firstOrCreate(['name' => 'Gestor de Proyectos']);
            if (!$role->hasPermissionTo($permission)) $role->givePermissionTo($permission);

            $user = User::firstOrCreate(
                ['email' => 'proyectos@gadium.com'],
                ['name' => 'Usuario Proyectos', 'password' => Hash::make('password'), 'email_verified_at' => now()]
            );
            if (!$user->hasRole('Gestor de Proyectos')) $user->assignRole($role);
        });
    }
}
