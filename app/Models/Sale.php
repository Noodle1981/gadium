<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Contracts\Auditable;

class Sale extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    protected $fillable = [
        'fecha',
        'client_id',
        'cliente_nombre',
        'monto',
        'moneda',
        'comprobante',
        'hash',
        // Columnas Tango adicionales
        'cod_cli',
        'n_remito',
        't_comp',
        'cond_vta',
        'porc_desc',
        'cotiz',
        'cod_transp',
        'nom_transp',
        'cod_articu',
        'descripcio',
        'cod_dep',
        'um',
        'cantidad',
        'precio',
        'tot_s_imp',
        'n_comp_rem',
        'cant_rem',
        'fecha_rem',
    ];

    protected $casts = [
        'fecha' => 'date',
        'monto' => 'decimal:2',
        'porc_desc' => 'decimal:2',
        'cotiz' => 'decimal:2',
        'cantidad' => 'decimal:2',
        'precio' => 'decimal:2',
        'tot_s_imp' => 'decimal:2',
        'cant_rem' => 'decimal:2',
        'fecha_rem' => 'date',
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

