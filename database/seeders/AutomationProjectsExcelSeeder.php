<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\Client;
use Illuminate\Database\Seeder;
use PhpOffice\PhpSpreadsheet\IOFactory;

class AutomationProjectsExcelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Importa proyectos desde el Excel "Proyectos Automatización.xlsx"
     * y los inserta/actualiza en la tabla projects.
     */
    public function run(): void
    {
        $excelPath = base_path('doc/excel pruebas/proyectos/Proyectos Automatización.xlsx');
        
        if (!file_exists($excelPath)) {
            $this->command->error("❌ Archivo no encontrado: {$excelPath}");
            return;
        }
        
        $this->command->info('📂 Leyendo Excel: Proyectos Automatización.xlsx');
        
        $spreadsheet = IOFactory::load($excelPath);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();
        
        // Saltar header
        array_shift($rows);
        
        $this->command->info("📊 Total de proyectos en Excel: " . count($rows));
        
        $bar = $this->command->getOutput()->createProgressBar(count($rows));
        $bar->start();
        
        $created = 0;
        $updated = 0;
        
        foreach ($rows as $row) {
            $proyectoId = trim($row[0]);
            $clienteNombre = trim($row[1]);
            $descripcion = trim($row[2]);
            $fat = trim($row[3]);
            $pem = trim($row[4]);
            
            if (empty($proyectoId)) {
                continue;
            }
            
            // Buscar o crear cliente
            $client = Client::firstOrCreate(
                ['nombre' => $clienteNombre]
            );
            
            // Buscar o crear proyecto
            $project = Project::updateOrCreate(
                ['id' => $proyectoId],
                [
                    'name' => $descripcion ?: "Proyecto {$proyectoId}",
                    'client_id' => $client->id,
                    'status' => 'activo',
                    'quality_status' => 'normal',
                ]
            );
            
            if ($project->wasRecentlyCreated) {
                $created++;
            } else {
                $updated++;
            }
            
            $bar->advance();
        }
        
        $bar->finish();
        $this->command->newLine(2);
        
        $this->command->info("✅ Proyectos creados: {$created}");
        $this->command->info("🔄 Proyectos actualizados: {$updated}");
        $this->command->info("🎯 Total en tabla projects: " . Project::count());
    }
}
