<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Budget extends Model
{
    protected $fillable = [
        'fecha',
        'client_id',
        'cliente_nombre',
        'monto',
        'moneda',
        'comprobante',
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
     * Basado en: fecha + cliente_nombre + comprobante + monto
     */
    public static function generateHash(string $fecha, string $clienteNombre, string $comprobante, float $monto): string
    {
        // Asegurar formato de fecha YYYY-MM-DD
        $fecha = substr($fecha, 0, 10);
        
        // Normalizar nombre de cliente
        $clienteNormalizado = Client::normalizeClientName($clienteNombre);
        
        $data = $fecha . '|' . $clienteNormalizado . '|' . $comprobante . '|' . number_format($monto, 2, '.', '');
        
        return hash('sha256', $data);
    }
}
