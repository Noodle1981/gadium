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
        // Usuario Super Admin
        $superAdmin = User::create([
            'name' => 'Super Administrador',
            'email' => 'admin@gaudium.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $superAdmin->assignRole('Super Admin');

        // Usuario Admin
        $admin = User::create([
            'name' => 'Administrador',
            'email' => 'administrador@gaudium.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $admin->assignRole('Admin');

        // Usuario Manager
        $manager = User::create([
            'name' => 'Gerente',
            'email' => 'gerente@gaudium.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $manager->assignRole('Manager');

        // Usuario Demo
        $demoUser = User::create([
            'name' => 'Carlos',
            'email' => 'carlos@carlitos.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $demoUser->assignRole('Manager');


    }
}
