<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\ClientSatisfactionResponse;
use App\Services\ClientSatisfactionCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ClientSatisfactionCalculatorTest extends TestCase
{
    use RefreshDatabase;

    public function test_calculate_metrics_returns_correct_values()
    {
        // Arrange
        // Crear 11 respuestas simuladas con ratings de 5 en todo
        // Total esperado = 11 * 5 = 55
        // Total obtenido = 11 * 5 = 55
        // Porcentaje = 100%
        
        $responses = collect();
        for ($i = 0; $i < 11; $i++) {
            $responses->push(new ClientSatisfactionResponse([
                'pregunta_1' => 5,
                'pregunta_2' => 5,
                'pregunta_3' => 5,
                'pregunta_4' => 5,
            ]));
        }

        $calculator = new ClientSatisfactionCalculator();
        $metrics = $calculator->calculateMetrics($responses);

        $this->assertEquals(55, $metrics[1]['expected']);
        $this->assertEquals(55, $metrics[1]['obtained']);
        $this->assertEquals(100, $metrics[1]['percentage']);
    }

    public function test_calculate_metrics_with_mixed_values()
    {
        // Arrange
        // 2 respuestas
        // R1: 5, 4, 3, 2
        // R2: 4, 5, 2, 3
        
        // P1 Esperado: 2*5 = 10. Obtenido: 5+4 = 9. %: 90
        // P2 Esperado: 10. Obtenido: 4+5 = 9. %: 90
        // P3 Esperado: 10. Obtenido: 3+2 = 5. %: 50
        // P4 Esperado: 10. Obtenido: 2+3 = 5. %: 50
        
        $responses = collect([
            new ClientSatisfactionResponse(['pregunta_1' => 5, 'pregunta_2' => 4, 'pregunta_3' => 3, 'pregunta_4' => 2]),
            new ClientSatisfactionResponse(['pregunta_1' => 4, 'pregunta_2' => 5, 'pregunta_3' => 2, 'pregunta_4' => 3]),
        ]);

        $calculator = new ClientSatisfactionCalculator();
        $metrics = $calculator->calculateMetrics($responses);

        $this->assertEquals(10, $metrics[1]['expected']);
        $this->assertEquals(9, $metrics[1]['obtained']);
        $this->assertEquals(90, $metrics[1]['percentage']);
        
        $this->assertEquals(50, $metrics[3]['percentage']);
    }
}
