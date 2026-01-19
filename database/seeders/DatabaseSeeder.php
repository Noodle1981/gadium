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

        // ÉPICA 01: Gestión de Accesos y Gobierno de Datos - UNIFICADA
        $this->command->info('📦 Cargando ÉPICA 01: Gestión de Accesos y Gobierno de Datos (Unificado)');
        
        // Seeder Universal (Permisos -> Roles -> Usuarios)
        $this->call(UniversalCredentialsSeeder::class);
        
        $this->command->info('✅ ÉPICA 01 completada: Sistema de credenciales inicializado.');
        $this->command->newLine();

        // ÉPICA 02: Motor de Ingesta y Normalización de Datos
        // $this->command->info('📦 Cargando ÉPICA 02: Motor de Ingesta y Normalización de Datos');
        // $this->call(Epica2Seeder::class);
        // $this->command->newLine();

        // ÉPICA 06: Integración con Grafana y Experiencia Unificada
        // $this->command->info('📦 Verificando ÉPICA 06: Integración con Grafana y Experiencia Unificada');
        // $this->command->line('   → Estructura de agregación de datos verificada.');
        // $this->command->line('   → UI con Sidebar Dinámico activa.');
        // $this->command->info('✅ ÉPICA 06 completada: Performance BI y Navegación Unificada');
        // $this->command->newLine();

        $this->command->info('✨ Seeders completados exitosamente!');
    }
}
