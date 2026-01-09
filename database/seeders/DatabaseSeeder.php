<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('🚀 Iniciando seeders de Gadium...');
        $this->command->newLine();

        // ÉPICA 01: Gestión de Accesos y Gobierno de Datos
        $this->command->info('📦 Cargando ÉPICA 01: Gestión de Accesos y Gobierno de Datos');
        $this->command->line('   → Creando permisos del sistema...');
        $this->call(PermissionSeeder::class);
        
        $this->command->line('   → Creando roles (Super Admin, Admin, Manager, Viewer)...');
        $this->call(RoleSeeder::class);
        
        $this->command->line('   → Creando usuarios de prueba...');
        $this->call(UserSeeder::class);
        
        $this->command->info('✅ ÉPICA 01 completada: 46 permisos, 4 roles, 4 usuarios');
        $this->command->newLine();

        $this->command->info('✨ Seeders completados exitosamente!');
    }
}
