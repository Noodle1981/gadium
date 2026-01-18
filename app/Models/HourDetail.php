<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HourDetail extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'hour_details';

    protected $fillable = [
        'dia',
        'fecha',
        'ano',
        'mes',
        'personal',
        'funcion',
        'proyecto',
        'horas_ponderadas',
        'ponderador',
        'hs',
        'hs_comun',
        'hs_50',
        'hs_100',
        'hs_viaje',
        'hs_pernoctada',
        'hs_adeudadas',
        'vianda',
        'observacion',
        'programacion',
        'hash',
        'user_id',
        'job_function_id',
        'guardia_id',
    ];

    protected $casts = [
        'fecha' => 'date',
        'ano' => 'integer',
        'mes' => 'integer',
        'horas_ponderadas' => 'decimal:4',
        'ponderador' => 'decimal:4',
        'hs' => 'decimal:2',
        'hs_comun' => 'decimal:2',
        'hs_50' => 'decimal:2',
        'hs_100' => 'decimal:2',
        'hs_viaje' => 'decimal:2',
        'hs_adeudadas' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function jobFunction()
    {
        return $this->belongsTo(JobFunction::class);
    }

    public function guardia()
    {
        return $this->belongsTo(Guardia::class);
    }

    /**
     * Generar hash único para idempotencia
     * Basado en: fecha + personal + proyecto + hs
     */
    public static function generateHash(string $fecha, string $personal, string $proyecto, float $hs): string
    {
        // Asegurar formato de fecha YYYY-MM-DD
        $fecha = substr($fecha, 0, 10);
        
        // Normalizar strings
        $personal = trim($personal);
        $proyecto = trim($proyecto);
        
        $data = $fecha . '|' . $personal . '|' . $proyecto . '|' . number_format($hs, 2, '.', '');
        
        return hash('sha256', $data);
    }

    /**
     * Verificar si existe un registro con el mismo hash
     */
    public static function existsByHash(string $hash): bool
    {
        return self::where('hash', $hash)->exists();
    }
}
