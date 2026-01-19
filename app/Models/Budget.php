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
        // Columnas adicionales de Presupuestos
        'centro_costo',
        'cost_center_id',
        'nombre_proyecto',
        'project_id',
        'fecha_oc',
        'fecha_estimada_culminacion',
        'estado_proyecto_dias',
        'fecha_culminacion_real',
        'estado',
        'enviado_facturar',
        'nro_factura',
        'porc_facturacion',
        'saldo',
        'horas_ponderadas',
    ];

    protected $casts = [
        'fecha' => 'date',
        'monto' => 'decimal:2',
        'fecha_oc' => 'date',
        'fecha_estimada_culminacion' => 'date',
        'estado_proyecto_dias' => 'integer',
        'fecha_culminacion_real' => 'date',
        'saldo' => 'decimal:2',
        'horas_ponderadas' => 'decimal:2',
    ];

    /**
     * Relación con cliente
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Relación con proyecto
     */
    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id', 'id');
    }

    /**
     * Relación con centro de costo
     */
    public function costCenter()
    {
        return $this->belongsTo(CostCenter::class);
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

    /**
     * Verificar si un presupuesto con este hash ya existe
     */
    public static function existsByHash(string $hash): bool
    {
        return self::where('hash', $hash)->exists();
    }
}
