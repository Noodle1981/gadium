<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class BoardsModuleSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $permission = Permission::firstOrCreate(['name' => 'view_boards']);
            $role = Role::firstOrCreate(['name' => 'Gestor de Tableros']);
            if (!$role->hasPermissionTo($permission)) $role->givePermissionTo($permission);

            $user = User::firstOrCreate(
                ['email' => 'tableros@gadium.com'],
                ['name' => 'Usuario Tableros', 'password' => Hash::make('password'), 'email_verified_at' => now()]
            );
            if (!$user->hasRole('Gestor de Tableros')) $user->assignRole($role);
        });
    }
}
