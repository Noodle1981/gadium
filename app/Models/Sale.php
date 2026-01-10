<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sale extends Model
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
     * Basado en: fecha + cliente_normalizado + comprobante + monto
     */
    public static function generateHash(string $fecha, string $clienteNombre, string $comprobante, float $monto): string
    {
        $normalized = Client::normalizeClientName($clienteNombre);
        $data = $fecha . '|' . $normalized . '|' . $comprobante . '|' . number_format($monto, 2, '.', '');
        
        return hash('sha256', $data);
    }

    /**
     * Verificar si existe una venta con el mismo hash
     */
    public static function existsByHash(string $hash): bool
    {
        return self::where('hash', $hash)->exists();
    }
}

