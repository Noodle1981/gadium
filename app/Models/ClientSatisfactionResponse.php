<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use OwenIt\Auditing\Contracts\Auditable;

class ClientSatisfactionResponse extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'client_satisfaction_responses';

    protected $fillable = [
        'fecha',
        'client_id',
        'cliente_nombre',
        'proyecto',
        'pregunta_1', // Satisfacción Obra/Producto (1-5)
        'pregunta_2', // Desempeño Técnico (1-5)
        'pregunta_3', // Respuestas a necesidades (1-5)
        'pregunta_4', // Plazo de ejecución (1-5)
        'hash',
    ];

    protected $casts = [
        'fecha' => 'date',
        'pregunta_1' => 'integer',
        'pregunta_2' => 'integer',
        'pregunta_3' => 'integer',
        'pregunta_4' => 'integer',
    ];

    /**
     * Relación con el cliente normalizado
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Scope para filtrar por cliente
     */
    public function scopeByClient($query, $clientId)
    {
        if ($clientId) {
            return $query->where('client_id', $clientId);
        }
        return $query;
    }

    /**
     * Generar hash para idempotencia
     * Basado en: fecha + cliente + proyecto
     */
    public static function generateHash($fecha, $cliente, $proyecto, $p1, $p2, $p3, $p4): string
    {
        $data = $fecha . '|' . strtolower(trim($cliente)) . '|' . strtolower(trim($proyecto)) . '|' . $p1 . $p2 . $p3 . $p4;
        return hash('sha256', $data);
    }

    /**
     * Verificar existencia por hash
     */
    public static function existsByHash(string $hash): bool
    {
        return self::where('hash', $hash)->exists();
    }
}
