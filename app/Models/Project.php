<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasFactory, SoftDeletes;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'name',
        'client_id',
        'status',
        'quality_status',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function manufacturingLogs()
    {
        return $this->hasMany(ManufacturingLog::class);
    }

    /**
     * Calcula la tasa de error acumulada del proyecto.
     * Tasa (%) = (Sumatoria de Correcciones / Sumatoria de Unidades Producidas) * 100
     */
    public function getErrorRateAttribute(): float
    {
        $totalProduced = $this->manufacturingLogs()->sum('units_produced');
        if ($totalProduced === 0) return 0;

        $totalCorrections = $this->manufacturingLogs()->sum('correction_documents');
        return round(($totalCorrections / $totalProduced) * 100, 2);
    }
}
