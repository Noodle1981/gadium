<?php

namespace App\Services;

use App\Models\ClientSatisfactionResponse;
use App\Models\ClientSatisfactionAnalysis;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ClientSatisfactionCalculator
{
    // Valor esperado por defecto para un set de respuestas completas (si fueran 11 respuestas)
    // Pero la lógica del Excel es: Valor Esperado = 5 * Cantidad de Respuestas para esa pregunta
    // En el Excel ejemplo: 11 respuestas * 5 = 55.
    // Nosotros debemos calcular dinámicamente: Esperado = Count(Responses) * 5.
    
    public function calculateMetrics($responses)
    {
        // $responses es una colección de ClientSatisfactionResponse
        $count = $responses->count();
        if ($count === 0) return [];

        $metrics = [];
        
        // Iterar por las 4 preguntas
        for ($i = 1; $i <= 4; $i++) {
            $colName = "pregunta_{$i}";
            
            // Esperado: 5 puntos * cantidad de respuestas
            $expected = $count * 5;
            
            // Obtenido: Suma de los ratings
            $obtained = $responses->sum($colName);
            
            // Porcentaje: (Obtenido / Esperado) * 100
            $percentage = $expected > 0 ? ($obtained / $expected) * 100 : 0;
            
            $metrics[$i] = [
                'expected' => $expected,
                'obtained' => $obtained,
                'percentage' => round($percentage, 2),
            ];
        }
        
        return $metrics;
    }

    /**
     * Actualiza las estadísticas mensuales (Globales y Por Cliente)
     * basándose en la fecha de las respuestas.
     * 
     * @param string|Carbon $date Fecha referencia (se tomará el mes y año)
     * @param int|null $specificClientId Si se pasa ID, solo actualiza ese cliente. Si null, actualiza todo.
     */
    public function updateMonthlyStats($dateData, $specificClientId = null)
    {
        $date = Carbon::parse($dateData);
        $period = $date->format('Y-m');
        $startOfMonth = $date->copy()->startOfMonth();
        $endOfMonth = $date->copy()->endOfMonth();

        // 1. Actualizar Global (client_id = null)
        if ($specificClientId === null) {
            $globalResponses = ClientSatisfactionResponse::whereBetween('fecha', [$startOfMonth, $endOfMonth])->get();
            $this->persistAnalysis($period, null, $globalResponses);
        }

        // 2. Actualizar Por Cliente
        // Si nos pasan un cliente específico, solo actualizamos ese.
        // Si no, buscamos todos los clientes que tuvieron actividad este mes.
        
        if ($specificClientId) {
            $clientids = [$specificClientId];
        } else {
            // IDs de clientes con respuestas en este mes
            $clientids = ClientSatisfactionResponse::whereBetween('fecha', [$startOfMonth, $endOfMonth])
                ->distinct()
                ->pluck('client_id')
                ->toArray();
        }

        foreach ($clientids as $clientId) {
            $clientResponses = ClientSatisfactionResponse::whereBetween('fecha', [$startOfMonth, $endOfMonth])
                ->where('client_id', $clientId)
                ->get();
            
            $this->persistAnalysis($period, $clientId, $clientResponses);
        }
    }

    protected function persistAnalysis($period, $clientId, $responses)
    {
        $metrics = $this->calculateMetrics($responses);
        $totalResponses = $responses->count();

        if ($totalResponses === 0) {
            // Si no hay respuestas (borraron todas?), se podría eliminar el análisis o dejar en 0.
            // Optamos por update or create con ceros.
             ClientSatisfactionAnalysis::updateOrCreate(
                ['periodo' => $period, 'client_id' => $clientId],
                [
                    'total_respuestas' => 0,
                    'pregunta_1_esperado' => 0, 'pregunta_1_obtenido' => 0, 'pregunta_1_porcentaje' => 0,
                    'pregunta_2_esperado' => 0, 'pregunta_2_obtenido' => 0, 'pregunta_2_porcentaje' => 0,
                    'pregunta_3_esperado' => 0, 'pregunta_3_obtenido' => 0, 'pregunta_3_porcentaje' => 0,
                    'pregunta_4_esperado' => 0, 'pregunta_4_obtenido' => 0, 'pregunta_4_porcentaje' => 0,
                ]
            );
            return;
        }

        ClientSatisfactionAnalysis::updateOrCreate(
            ['periodo' => $period, 'client_id' => $clientId],
            [
                'total_respuestas' => $totalResponses,
                
                'pregunta_1_esperado' => $metrics[1]['expected'],
                'pregunta_1_obtenido' => $metrics[1]['obtained'],
                'pregunta_1_porcentaje' => $metrics[1]['percentage'],

                'pregunta_2_esperado' => $metrics[2]['expected'],
                'pregunta_2_obtenido' => $metrics[2]['obtained'],
                'pregunta_2_porcentaje' => $metrics[2]['percentage'],

                'pregunta_3_esperado' => $metrics[3]['expected'],
                'pregunta_3_obtenido' => $metrics[3]['obtained'],
                'pregunta_3_porcentaje' => $metrics[3]['percentage'],

                'pregunta_4_esperado' => $metrics[4]['expected'],
                'pregunta_4_obtenido' => $metrics[4]['obtained'],
                'pregunta_4_porcentaje' => $metrics[4]['percentage'],
            ]
        );
    }
}
