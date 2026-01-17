<?php

namespace App\Services;

use App\Models\StaffSatisfactionResponse;
use App\Models\StaffSatisfactionAnalysis;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class StaffSatisfactionCalculator
{
    /**
     * Calculate aggregated metrics from a collection of responses.
     */
    public function calculateMetrics(Collection $responses): array
    {
        $total = $responses->count();
        
        $metrics = [
            'total' => $total,
            'p1' => [
                'mal' => $responses->where('p1_mal', true)->count(),
                'normal' => $responses->where('p1_normal', true)->count(),
                'bien' => $responses->where('p1_bien', true)->count(),
            ],
            'p2' => [
                'mal' => $responses->where('p2_mal', true)->count(),
                'normal' => $responses->where('p2_normal', true)->count(),
                'bien' => $responses->where('p2_bien', true)->count(),
            ],
            'p3' => [
                'mal' => $responses->where('p3_mal', true)->count(),
                'normal' => $responses->where('p3_normal', true)->count(),
                'bien' => $responses->where('p3_bien', true)->count(),
            ],
            'p4' => [
                'mal' => $responses->where('p4_mal', true)->count(),
                'normal' => $responses->where('p4_normal', true)->count(),
                'bien' => $responses->where('p4_bien', true)->count(),
            ],
        ];

        return $metrics;
    }

    /**
     * Recalculate and update stats for a specific period (YYYY-MM).
     * If $period is null, it recalculates based on the whole dataset or defaults to current month.
     * In this module, we usually group by 'period' field (YYYY-MM) derived from 'fecha'.
     */
    public function updateMonthlyStats(?string $periodo = null): void
    {
        if (!$periodo) {
            $periodo = now()->format('Y-m');
        }

        // Get responses for this period
        // Need to parse period 'YYYY-MM' to start/end dates
        try {
            $date = Carbon::createFromFormat('Y-m', $periodo);
            $start = $date->copy()->startOfMonth();
            $end = $date->copy()->endOfMonth();
            
            $responses = StaffSatisfactionResponse::whereBetween('fecha', [$start, $end])->get();
        } catch (\Exception $e) {
            // Fallback if date parsing fails or logic requires full recalc
             $responses = StaffSatisfactionResponse::all(); 
        }

        $metrics = $this->calculateMetrics($responses);
        $total = $metrics['total'];

        // Helper closure for percentage
        $pct = fn($count) => $total > 0 ? round(($count / $total) * 100, 2) : 0;

        StaffSatisfactionAnalysis::updateOrCreate(
            ['periodo' => $periodo],
            [
                // P1
                'p1_mal_count' => $metrics['p1']['mal'],
                'p1_normal_count' => $metrics['p1']['normal'],
                'p1_bien_count' => $metrics['p1']['bien'],
                'p1_mal_pct' => $pct($metrics['p1']['mal']),
                'p1_normal_pct' => $pct($metrics['p1']['normal']),
                'p1_bien_pct' => $pct($metrics['p1']['bien']),
                
                // P2
                'p2_mal_count' => $metrics['p2']['mal'],
                'p2_normal_count' => $metrics['p2']['normal'],
                'p2_bien_count' => $metrics['p2']['bien'],
                'p2_mal_pct' => $pct($metrics['p2']['mal']),
                'p2_normal_pct' => $pct($metrics['p2']['normal']),
                'p2_bien_pct' => $pct($metrics['p2']['bien']),

                // P3
                'p3_mal_count' => $metrics['p3']['mal'],
                'p3_normal_count' => $metrics['p3']['normal'],
                'p3_bien_count' => $metrics['p3']['bien'],
                'p3_mal_pct' => $pct($metrics['p3']['mal']),
                'p3_normal_pct' => $pct($metrics['p3']['normal']),
                'p3_bien_pct' => $pct($metrics['p3']['bien']),

                // P4
                'p4_mal_count' => $metrics['p4']['mal'],
                'p4_normal_count' => $metrics['p4']['normal'],
                'p4_bien_count' => $metrics['p4']['bien'],
                'p4_mal_pct' => $pct($metrics['p4']['mal']),
                'p4_normal_pct' => $pct($metrics['p4']['normal']),
                'p4_bien_pct' => $pct($metrics['p4']['bien']),
                
                'total_respuestas' => $total,
            ]
        );
    }
}
