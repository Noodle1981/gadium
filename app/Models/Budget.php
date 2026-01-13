<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Budget extends Model
{
    protected $fillable = [
        'fecha',
        'client_id',
        'monto',
        'moneda',
        'hash',
    ];

    protected $casts = [
        'fecha' => 'date',
        'monto' => 'decimal:2',
    ];

    /**
     * Relación con cliente
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Generar hash único para idempotencia
     * Basado en: fecha + cliente_id + monto + moneda
     * Nota: Usamos client_id aquí asumiendo que ya se resolvió el cliente.
     */
    public static function generateHash(string $fecha, int $clientId, float $monto, string $moneda): string
    {
        // Asegurar formato de fecha YYYY-MM-DD
        $fecha = substr($fecha, 0, 10);
        
        $data = $fecha . '|' . $clientId . '|' . number_format($monto, 2, '.', '') . '|' . $moneda;
        
        return hash('sha256', $data);
    }
}
