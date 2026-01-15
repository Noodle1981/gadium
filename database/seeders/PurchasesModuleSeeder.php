<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class PurchasesModuleSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            // Permissions
            $permissions = [
                'view_purchases',
                'create_purchases',
                'edit_purchases',
            ];

            foreach ($permissions as $permName) {
                Permission::firstOrCreate(['name' => $permName]);
            }

            // Role
            $role = Role::firstOrCreate(['name' => 'Gestor de Compras']);
            
            // Assign permissions to role
            $role->syncPermissions($permissions);

            // User
            $user = User::firstOrCreate(
                ['email' => 'compras@gadium.com'],
                ['name' => 'Usuario Compras', 'password' => Hash::make('password'), 'email_verified_at' => now()]
            );
            
            if (!$user->hasRole('Gestor de Compras')) {
                $user->assignRole($role);
            }
        });
    }
}
