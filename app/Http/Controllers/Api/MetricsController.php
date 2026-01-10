<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ParetoAnalysisService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class MetricsController extends Controller
{
    public function __construct(
        protected ParetoAnalysisService $paretoService
    ) {}

    /**
     * Endpoint: GET /api/v1/metrics/sales-concentration
     * Retorna análisis de Pareto (80/20) para Grafana.
     */
    public function salesConcentration(Request $request)
    {
        try {
            // Validar parámetros (default: mes actual)
            $startDate = $request->input('from') ? Carbon::parse($request->input('from')) : now()->startOfMonth();
            $endDate = $request->input('to') ? Carbon::parse($request->input('to')) : now()->endOfMonth();

            $data = $this->paretoService->calculateConcentration($startDate, $endDate);

            return response()->json([
                'status' => 'success',
                'meta' => [
                    'generated_at' => now()->toIso8601String(),
                    'source' => 'Gadium BI Engine'
                ],
                'data' => $data
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error calculating Pareto metrics: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Endpoint: GET /api/v1/metrics/production-efficiency
     * MOCK para visualización temprana en Grafana.
     * Simula datos de horas ponderadas vs objetivo.
     */
    public function productionEfficiency(Request $request)
    {
        // Simulación de datos mensuales
        $months = [];
        $currentMonth = now()->startOfYear();
        
        for ($i = 0; $i < 12; $i++) {
            // Simular una tendencia estacional: más baja en verano, alta a mitad de año
            $baseEfficiency = 70 + (sin($i / 2) * 20); // Oscila entre 50 y 90
            $randomVar = rand(-5, 5);
            
            $months[] = [
                'month' => $currentMonth->format('Y-m'),
                'target_hours' => 3394,
                'actual_weighted_hours' => round(3394 * (($baseEfficiency + $randomVar) / 100), 2),
                'efficiency_percentage' => round($baseEfficiency + $randomVar, 2)
            ];
            
            $currentMonth->addMonth();
        }

        return response()->json([
            'status' => 'success_mock',
            'meta' => [
                'note' => 'MOCK DATA for Dashboard Design Phase',
                'generated_at' => now()->toIso8601String()
            ],
            'data' => $months
        ]);
    }
}
