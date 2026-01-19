<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'name_normalized',
        'tax_id',
        'address',
        'email',
        'phone',
        'status',
    ];

    /**
     * Relación con detalles de compras
     */
    public function purchaseDetails()
    {
        return $this->hasMany(PurchaseDetail::class);
    }

    /**
     * Relación con aliases
     */
    public function aliases()
    {
        return $this->hasMany(SupplierAlias::class);
    }

    /**
     * Normalizar nombre de proveedor
     * Convierte a minúsculas, elimina espacios extras y caracteres especiales
     */
    public static function normalizeSupplierName(string $name): string
    {
        // Convertir a minúsculas
        $normalized = mb_strtolower($name, 'UTF-8');
        
        // Eliminar puntos, comas y guiones
        $normalized = str_replace(['.', ',', '-', '_'], ' ', $normalized);
        
        // Eliminar espacios múltiples
        $normalized = preg_replace('/\s+/', ' ', $normalized);
        
        // Trim
        $normalized = trim($normalized);
        
        return $normalized;
    }

    /**
     * Boot method para auto-normalizar al crear/actualizar
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($supplier) {
            if (isset($supplier->name)) {
                $supplier->name_normalized = self::normalizeSupplierName($supplier->name);
            }
        });
    }
}
