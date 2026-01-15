<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\HourDetail;
use Carbon\Carbon;

class HourDetailsSeeder extends Seeder
{
    public function run(): void
    {
        // Limpiar tabla
        HourDetail::truncate();
        
        $startDate = Carbon::now()->startOfMonth();
        $personalList = ['Juan Perez', 'Maria Garcia', 'Carlos Lopez', 'Ana Martinez'];
        $projectList = ['PRJ-001 Edificio Central', 'PRJ-002 Plaza Norte', 'PRJ-003 Mantenimiento Sur'];
        $functionList = ['O. Especializado', 'Ayudante', 'Capataz'];

        // Crear 50 registros
        for ($i = 0; $i < 50; $i++) {
            $date = $startDate->copy()->addDays(rand(0, 20));
            $personal = $personalList[array_rand($personalList)];
            $proyecto = $projectList[array_rand($projectList)];
            $hs = rand(4, 10) + (rand(0, 1) ? 0.5 : 0); // 8.0 or 8.5
            
            $hash = HourDetail::generateHash($date->format('Y-m-d'), $personal, $proyecto, $hs);
            
            if (HourDetail::existsByHash($hash)) continue;
            
            setlocale(LC_TIME, 'es_ES.UTF-8');
            $dia = strtoupper($date->locale('es')->dayName);
            
            HourDetail::create([
                'dia' => $dia,
                'fecha' => $date->format('Y-m-d'),
                'ano' => $date->year,
                'mes' => $date->month,
                'personal' => $personal,
                'funcion' => $functionList[array_rand($functionList)],
                'proyecto' => $proyecto,
                'horas_ponderadas' => $hs, // Simple logic for seeder
                'ponderador' => 1,
                'hs' => $hs,
                'hs_comun' => $hs,
                'hs_50' => 0,
                'hs_100' => 0,
                'hs_viaje' => 0,
                'hs_pernoctada' => 'No',
                'hs_adeudadas' => 0,
                'vianda' => '0',
                'observacion' => 'Carga inicial de prueba',
                'programacion' => '',
                'hash' => $hash,
            ]);
        }
    }
}
