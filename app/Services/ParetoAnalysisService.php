<?php

namespace App\Services;

use App\Models\Sale;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class ParetoAnalysisService
{
    public function __construct(
        protected CurrencyService $currencyService
    ) {}

    /**
     * Calcula la concentración de ventas para un período dado aplicando la regla 80/20.
     * Retorna una estructura analítica con el top de clientes y métricas de riesgo.
     */
    public function calculateConcentration(Carbon $startDate, Carbon $endDate): array
    {
        // 1. Obtener todas las ventas del período
        // En una implementación real con millones de registros, esto usaría una tabla agregada (daily_kpi_aggregates).
        // Para esta versión MVP, agregamos directamente sobre la tabla sales.
        
        $sales = Sale::whereBetween('fecha', [$startDate, $endDate])->get();

        // 2. Normalizar ventas a ARS y Agrupar por Cliente
        $clientSales = $sales->groupBy('client_id')->map(function ($clientSales) {
            
            // Sumar valor normalizado
            $totalMonto = $clientSales->sum(function ($sale) {
                return $this->currencyService->convert($sale->monto, $sale->moneda ?: 'ARS', 'ARS');
            });

            // Recuperar nombre del cliente (asumiendo que todos tienen el mismo nombre duplicado o tomando el primero)
            // Idealmente usar la relación con el modelo Client
            $clientName = $clientSales->first()->client ? $clientSales->first()->client->nombre : 'Desconocido';

            return [
                'client_id' => $clientSales->first()->client_id,
                'client_name' => $clientName,
                'amount' => $totalMonto,
            ];
        });

        // 3. Ordenar de Mayor a Menor (Ranking)
        $rankedClients = $clientSales->sortByDesc('amount')->values();
        $totalRevenue = $rankedClients->sum('amount');

        if ($totalRevenue == 0) {
            return [
                'total_revenue' => 0,
                'pareto_ratio' => 0,
                'risk_level' => 'BAJO', // Sin ventas, sin riesgo de concentración
                'top_clients' => [],
                'details' => []
            ];
        }

        // 4. Calcular Pareto (Top 20%)
        $numClients = $rankedClients->count();
        $top20Count = ceil($numClients * 0.20);
        
        $top20Clients = $rankedClients->take($top20Count);
        $top20Revenue = $top20Clients->sum('amount');
        
        $concentrationPercentage = ($top20Revenue / $totalRevenue) * 100;

        // 5. Determinar Nivel de Riesgo
        // Si el 20% de los clientes genera > 80% de ventas -> Riesgo Alto de Dependencia
        $riskLevel = $concentrationPercentage > 80 ? 'ALTO' : 'NORMAL';

        return [
            'period' => [
                'start' => $startDate->toDateString(),
                'end' => $endDate->toDateString()
            ],
            'total_revenue' => round($totalRevenue, 2),
            'currency' => 'ARS', // Moneda de normalización
            'total_active_clients' => $numClients,
            'top_20_count' => $top20Count,
            'top_20_revenue' => round($top20Revenue, 2),
            'pareto_concentration_percentage' => round($concentrationPercentage, 2),
            'risk_level' => $riskLevel,
            'top_clients' => $top20Clients->map(function($client) use ($totalRevenue) {
                return [
                    'name' => $client['client_name'],
                    'revenue' => round($client['amount'], 2),
                    'share' => round(($client['amount'] / $totalRevenue) * 100, 2)
                ];
            })->values()->all()
        ];
    }
}
