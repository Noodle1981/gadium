<?php

namespace Database\Seeders;

use App\Models\CostCenter;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CostCentersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Extrae centros de costo únicos desde purchase_details y budgets
     * y los inserta en la tabla cost_centers.
     */
    public function run(): void
    {
        $this->command->info('🔍 Extrayendo centros de costo únicos desde tablas transaccionales...');
        
        $costCenters = collect();
        
        // 1. Desde purchase_details (campo: cc)
        $this->command->info('  → Analizando purchase_details...');
        $fromPurchases = DB::table('purchase_details')
            ->select('cc as code')
            ->distinct()
            ->whereNotNull('cc')
            ->where('cc', '!=', '')
            ->get();
        
        $costCenters = $costCenters->merge($fromPurchases->map(fn($cc) => [
            'code' => trim($cc->code),
            'source' => 'purchase_details'
        ]));
        
        // 2. Desde budgets (campo: centro_costo) - si existe
        if (DB::getSchemaBuilder()->hasTable('budgets') && 
            DB::getSchemaBuilder()->hasColumn('budgets', 'centro_costo')) {
            
            $this->command->info('  → Analizando budgets...');
            $fromBudgets = DB::table('budgets')
                ->select('centro_costo as code')
                ->distinct()
                ->whereNotNull('centro_costo')
                ->where('centro_costo', '!=', '')
                ->get();
            
            $costCenters = $costCenters->merge($fromBudgets->map(fn($cc) => [
                'code' => trim($cc->code),
                'source' => 'budgets'
            ]));
        }
        
        // Eliminar duplicados por código (case-insensitive)
        $costCentersUnicos = $costCenters->unique(fn($cc) => strtoupper($cc['code']));
        
        $this->command->info("📊 Total de centros de costo únicos encontrados: {$costCentersUnicos->count()}");
        
        // Insertar centros de costo en la tabla cost_centers
        $bar = $this->command->getOutput()->createProgressBar($costCentersUnicos->count());
        $bar->start();
        
        $created = 0;
        $skipped = 0;
        
        foreach ($costCentersUnicos as $cc) {
            // Crear centro de costo si no existe
            $exists = CostCenter::where('code', $cc['code'])->exists();
            
            if (!$exists) {
                CostCenter::create([
                    'code' => $cc['code'],
                    'name' => "Centro de Costo {$cc['code']}",
                ]);
                $created++;
            } else {
                $skipped++;
            }
            
            $bar->advance();
        }
        
        $bar->finish();
        $this->command->newLine(2);
        
        $this->command->info("✅ Centros de costo creados: {$created}");
        $this->command->info("⏭️  Centros de costo omitidos (ya existían): {$skipped}");
        $this->command->info("🎯 Total en tabla cost_centers: " . CostCenter::count());
    }
}
