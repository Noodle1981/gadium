<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\Client;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProjectsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Extrae proyectos únicos desde hour_details, board_details y automation_projects
     * y los inserta en la tabla projects.
     */
    public function run(): void
    {
        $this->command->info('🔍 Extrayendo proyectos únicos desde tablas transaccionales...');
        
        // Crear cliente por defecto para proyectos sin cliente
        $defaultClient = Client::firstOrCreate(
            ['nombre' => 'Sin Cliente Asignado']
        );
        
        $proyectos = collect();
        
        // 1. Desde hour_details (campo: proyecto)
        $this->command->info('  → Analizando hour_details...');
        $fromHours = DB::table('hour_details')
            ->select('proyecto as code')
            ->distinct()
            ->whereNotNull('proyecto')
            ->where('proyecto', '!=', '')
            ->get();
        
        $proyectos = $proyectos->merge($fromHours->map(fn($p) => [
            'code' => trim($p->code),
            'name' => "Proyecto " . trim($p->code),
            'source' => 'hour_details'
        ]));
        
        // 2. Desde board_details (campos: proyecto_numero, descripcion_proyecto, cliente)
        $this->command->info('  → Analizando board_details...');
        $fromBoards = DB::table('board_details')
            ->select('proyecto_numero as code', 'descripcion_proyecto as name', 'cliente')
            ->distinct()
            ->whereNotNull('proyecto_numero')
            ->where('proyecto_numero', '!=', '')
            ->get();
        
        $proyectos = $proyectos->merge($fromBoards->map(fn($p) => [
            'code' => trim($p->code),
            'name' => trim($p->name) ?: "Proyecto " . trim($p->code),
            'cliente' => $p->cliente,
            'source' => 'board_details'
        ]));
        
        // 3. Desde automation_projects (campos: proyecto_id, proyecto_descripcion, cliente)
        $this->command->info('  → Analizando automation_projects...');
        $fromAutomation = DB::table('automation_projects')
            ->select('proyecto_id as code', 'proyecto_descripcion as name', 'cliente')
            ->distinct()
            ->whereNotNull('proyecto_id')
            ->where('proyecto_id', '!=', '')
            ->get();
        
        $proyectos = $proyectos->merge($fromAutomation->map(fn($p) => [
            'code' => trim($p->code),
            'name' => trim($p->name) ?: "Proyecto " . trim($p->code),
            'cliente' => $p->cliente,
            'source' => 'automation_projects'
        ]));
        
        // Eliminar duplicados por código (case-insensitive)
        $proyectosUnicos = $proyectos->unique(fn($p) => strtoupper($p['code']));
        
        $this->command->info("📊 Total de proyectos únicos encontrados: {$proyectosUnicos->count()}");
        
        // Insertar proyectos en la tabla projects
        $bar = $this->command->getOutput()->createProgressBar($proyectosUnicos->count());
        $bar->start();
        
        $created = 0;
        $skipped = 0;
        
        foreach ($proyectosUnicos as $proyecto) {
            // Intentar resolver client_id si hay nombre de cliente
            $clientId = $defaultClient->id;
            
            if (isset($proyecto['cliente']) && !empty($proyecto['cliente'])) {
                $client = Client::where('nombre', 'LIKE', '%' . trim($proyecto['cliente']) . '%')->first();
                if ($client) {
                    $clientId = $client->id;
                }
            }
            
            // Crear proyecto si no existe (usando code como id)
            $exists = Project::where('id', $proyecto['code'])->exists();
            
            if (!$exists) {
                Project::create([
                    'id' => $proyecto['code'],  // String primary key
                    'name' => $proyecto['name'],
                    'client_id' => $clientId,   // Required
                    'status' => 'activo',
                    'quality_status' => 'normal',
                ]);
                $created++;
            } else {
                $skipped++;
            }
            
            $bar->advance();
        }
        
        $bar->finish();
        $this->command->newLine(2);
        
        $this->command->info("✅ Proyectos creados: {$created}");
        $this->command->info("⏭️  Proyectos omitidos (ya existían): {$skipped}");
        $this->command->info("🎯 Total en tabla projects: " . Project::count());
    }
}
