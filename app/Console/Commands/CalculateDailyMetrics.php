<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Models\DailyMetricsAggregate;
use App\Services\ParetoAnalysisService;
use Carbon\Carbon;

class CalculateDailyMetrics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'metrics:calculate-daily {date? : Optional date to calculate (Y-m-d), defaults to yesterday}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculate and aggregate daily metrics for Grafana BI';

    /**
     * Execute the console command.
     */
    public function handle(ParetoAnalysisService $paretoService)
    {
        $dateStr = $this->argument('date') ?: now()->subDay()->format('Y-m-d');
        $date = Carbon::parse($dateStr);
        
        $this->info("🚀 Calculating metrics for: {$date->toDateString()}");

        // 1. Sales Concentration (Pareto) - Usando el mes hasta la fecha
        $startDate = $date->copy()->startOfMonth();
        $endDate = $date->copy()->endOfDay();
        
        try {
            $paretoData = $paretoService->calculateConcentration($startDate, $endDate);
            
            DailyMetricsAggregate::updateOrCreate(
                ['metric_date' => $date->toDateString(), 'metric_type' => 'sales_concentration'],
                ['metric_data' => $paretoData]
            );
            
            $this->line("   ✅ Sales Concentration: OK");
        } catch (\Exception $e) {
            $this->error("   ❌ Error calculating Pareto: " . $e->getMessage());
        }

        // 2. Production Efficiency (Mock por ahora, hasta tener lógica real en service)
        // Similar a MetricsController
        $baseEfficiency = 70 + (sin($date->month / 2) * 20);
        $randomVar = rand(-5, 5);
        $efficiencyData = [
            'target_hours' => 3394,
            'actual_weighted_hours' => round(3394 * (($baseEfficiency + $randomVar) / 100), 2),
            'efficiency_percentage' => round($baseEfficiency + $randomVar, 2)
        ];

        DailyMetricsAggregate::updateOrCreate(
            ['metric_date' => $date->toDateString(), 'metric_type' => 'production_efficiency'],
            ['metric_data' => $efficiencyData]
        );
        $this->line("   ✅ Production Efficiency: OK (Mock)");

        $this->info("✨ Metrics calculation completed!");
    }
}
