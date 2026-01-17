<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientSatisfactionAnalysis extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'client_satisfaction_analysis';

    protected $fillable = [
        'periodo', // YYYY-MM
        'client_id', // Nullable: null = Global, ID = Specific Client
        'total_respuestas',
        
        // Pregunta 1: Satisfacción Obra
        'pregunta_1_esperado',
        'pregunta_1_obtenido',
        'pregunta_1_porcentaje',
        
        // Pregunta 2: Desempeño Técnico
        'pregunta_2_esperado',
        'pregunta_2_obtenido',
        'pregunta_2_porcentaje',
        
        // Pregunta 3: Necesidades
        'pregunta_3_esperado',
        'pregunta_3_obtenido',
        'pregunta_3_porcentaje',
        
        // Pregunta 4: Plazo
        'pregunta_4_esperado',
        'pregunta_4_obtenido',
        'pregunta_4_porcentaje',
    ];

    protected $casts = [
        'pregunta_1_porcentaje' => 'decimal:2',
        'pregunta_2_porcentaje' => 'decimal:2',
        'pregunta_3_porcentaje' => 'decimal:2',
        'pregunta_4_porcentaje' => 'decimal:2',
    ];

    /**
     * Relación con cliente (puede ser null para métricas globales)
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
