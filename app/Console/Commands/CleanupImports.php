<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class CleanupImports extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:cleanup-imports';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Elimina archivos de importación y temporales de Livewire mayores a 24 horas';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🧹 Iniciando limpieza de archivos temporales...');

        $directories = [
            'imports',
            'livewire-tmp',
        ];

        $filesDeleted = 0;

        foreach ($directories as $directory) {
            if (!Storage::exists($directory)) {
                $this->warn("⚠️  El directorio {$directory} no existe, saltando...");
                continue;
            }

            $files = Storage::files($directory);
            $this->info("📂 Analizando {$directory} (" . count($files) . " archivos)...");

            foreach ($files as $file) {
                // Obtener timestamp de última modificación
                $lastModified = Storage::lastModified($file);
                $fileTime = Carbon::createFromTimestamp($lastModified);

                // Si el archivo tiene más de 24 horas
                if (now()->diffInHours($fileTime) >= 24) {
                    Storage::delete($file);
                    $this->line("   🗑️  Eliminado: {$file}");
                    $filesDeleted++;
                }
            }
        }

        $this->newLine();
        $this->info("✅ Limpieza completada. Total archivos eliminados: {$filesDeleted}");
    }
}
