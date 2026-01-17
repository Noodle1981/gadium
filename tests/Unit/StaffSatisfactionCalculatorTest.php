<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\StaffSatisfactionResponse;
use App\Models\StaffSatisfactionAnalysis;
use App\Services\StaffSatisfactionCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StaffSatisfactionCalculatorTest extends TestCase
{
    use RefreshDatabase;

    public function test_calculate_metrics_returns_correct_values()
    {
        // Arrange
        // Crear 10 respuestas
        // 5 respuestas: Todo Mal
        // 3 respuestas: Todo Normal
        // 2 respuestas: Todo Bien
        
        $responses = collect();
        
        // 5 Mal
        for ($i = 0; $i < 5; $i++) {
            $responses->push(new StaffSatisfactionResponse([
                'p1_mal' => true, 'p1_normal' => false, 'p1_bien' => false,
                'p2_mal' => true, 'p2_normal' => false, 'p2_bien' => false,
                'p3_mal' => true, 'p3_normal' => false, 'p3_bien' => false,
                'p4_mal' => true, 'p4_normal' => false, 'p4_bien' => false,
            ]));
        }
        
        // 3 Normal
        for ($i = 0; $i < 3; $i++) {
             $responses->push(new StaffSatisfactionResponse([
                'p1_mal' => false, 'p1_normal' => true, 'p1_bien' => false,
                // Mixed for P2 to test independence (let's keep simple first)
                'p2_mal' => false, 'p2_normal' => true, 'p2_bien' => false,
                'p3_mal' => false, 'p3_normal' => true, 'p3_bien' => false,
                'p4_mal' => false, 'p4_normal' => true, 'p4_bien' => false,
            ]));
        }

        // 2 Bien
        for ($i = 0; $i < 2; $i++) {
             $responses->push(new StaffSatisfactionResponse([
                'p1_mal' => false, 'p1_normal' => false, 'p1_bien' => true,
                'p2_mal' => false, 'p2_normal' => false, 'p2_bien' => true,
                'p3_mal' => false, 'p3_normal' => false, 'p3_bien' => true,
                'p4_mal' => false, 'p4_normal' => false, 'p4_bien' => true,
            ]));
        }

        $calculator = new StaffSatisfactionCalculator();
        $metrics = $calculator->calculateMetrics($responses);

        // Verify P1
        $this->assertEquals(5, $metrics['p1']['mal']);
        $this->assertEquals(3, $metrics['p1']['normal']);
        $this->assertEquals(2, $metrics['p1']['bien']);
        
        // percentages are calculated in updateMonthlyStats usually, but let's check raw counts first.
    }

    public function test_update_monthly_stats_persists_data()
    {
        // Create actual DB records
        StaffSatisfactionResponse::create([
            'personal' => 'User 1',
            'fecha' => '2026-01-15',
            'p1_mal' => true,
            'hash' => 'hash1'
        ]);
        
        StaffSatisfactionResponse::create([
            'personal' => 'User 2',
            'fecha' => '2026-01-15',
            'p1_bien' => true,
            'hash' => 'hash2'
        ]);

        $calculator = new StaffSatisfactionCalculator();
        $calculator->updateMonthlyStats('2026-01');

        $this->assertDatabaseHas('staff_satisfaction_analysis', [
            'periodo' => '2026-01',
            'total_respuestas' => 2,
            'p1_mal_count' => 1,
            'p1_bien_count' => 1,
            'p1_normal_count' => 0,
            'p1_mal_pct' => 50.00,
            'p1_bien_pct' => 50.00,
        ]);
    }
}
