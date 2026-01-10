<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DailyMetricsAggregate;
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

            // Intentar buscar en agregados primero (especialmente si es una fecha específica)
            // Para Pareto, usualmente queremos el acumulado del mes hasta hoy
            $aggregate = DailyMetricsAggregate::where('metric_type', 'sales_concentration')
                ->where('metric_date', $endDate->toDateString())
                ->first();

            if ($aggregate) {
                return response()->json([
                    'status' => 'success',
                    'meta' => [
                        'generated_at' => $aggregate->created_at->toIso8601String(),
                        'source' => 'Gadium Aggregated Store'
                    ],
                    'data' => $aggregate->metric_data
                ]);
            }

            // Fallback: Cálculo en tiempo real
            $data = $this->paretoService->calculateConcentration($startDate, $endDate);

            return response()->json([
                'status' => 'success',
                'meta' => [
                    'generated_at' => now()->toIso8601String(),
                    'source' => 'Gadium Realtime Engine (Fallback)'
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
        $startDate = $request->input('from') ? Carbon::parse($request->input('from')) : now()->startOfYear();
        $endDate = $request->input('to') ? Carbon::parse($request->input('to')) : now();

        // Intentar obtener datos reales (agregados)
        $aggregates = DailyMetricsAggregate::where('metric_type', 'production_efficiency')
            ->whereBetween('metric_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->orderBy('metric_date', 'asc')
            ->get();

        if ($aggregates->isNotEmpty()) {
            return response()->json([
                'status' => 'success',
                'meta' => [
                    'generated_at' => now()->toIso8601String(),
                    'source' => 'Gadium Production Engine'
                ],
                'data' => $aggregates->map(fn($a) => array_merge(['date' => $a->metric_date->toDateString()], $a->metric_data))
            ]);
        }

        // Simulación de datos mensuales (Mock legacy)
        $months = [];
        $currentMonth = $startDate->copy()->startOfMonth();
        
        while ($currentMonth->lte($endDate)) {
            $baseEfficiency = 70 + (sin($currentMonth->month / 2) * 20);
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
