<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class PurchaseDetail extends Model implements Auditable
{
    use HasFactory, \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'moneda',
        'cc',
        'ano',
        'empresa',
        'descripcion',
        'materiales_presupuestados',
        'materiales_comprados',
        'resto_valor',
        'resto_porcentaje',
        'porcentaje_facturacion',
        'supplier_id',
        'cost_center_id',
        'hash',
    ];

    protected $casts = [
        'ano' => 'integer',
        'materiales_presupuestados' => 'decimal:2',
        'materiales_comprados' => 'decimal:2',
        'resto_valor' => 'decimal:2',
        'resto_porcentaje' => 'decimal:2',
        'porcentaje_facturacion' => 'decimal:2',
    ];

    /**
     * Relación con proveedor
     */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Relación con centro de costo
     */
    public function costCenter()
    {
        return $this->belongsTo(CostCenter::class);
    }

    /**
     * Generar hash único para detectar duplicados
     */
    public static function generateHash(string $cc, string $ano, string $empresa, string $descripcion): string
    {
        $data = trim($cc) . '|' . trim($ano) . '|' . trim($empresa) . '|' . trim($descripcion);
        return hash('sha256', $data);
    }

    /**
     * Verificar si existe el hash
     */
    public static function existsByHash(string $hash): bool
    {
        return self::where('hash', $hash)->exists();
    }
}
