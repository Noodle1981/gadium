<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class BoardDetail extends Model implements Auditable
{
    use HasFactory, \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'ano',
        'proyecto_numero',
        'cliente',
        'descripcion_proyecto',
        'project_id',
        'client_id',
        'columnas',
        'gabinetes',
        'potencia',
        'pot_control',
        'control',
        'intervencion',
        'documento_correccion_fallas',
        'hash',
    ];

    protected $casts = [
        'ano' => 'integer',
        'columnas' => 'integer',
        'gabinetes' => 'integer',
        'potencia' => 'integer',
        'pot_control' => 'integer',
        'control' => 'integer',
        'intervencion' => 'integer',
        'documento_correccion_fallas' => 'integer',
    ];

    /**
     * Relación con proyecto
     */
    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id', 'id');
    }

    /**
     * Relación con cliente
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Generate a unique hash for duplication checking.
     */
    public static function generateHash(int $ano, string $proyectoNumero, string $cliente, string $descripcionProyecto): string
    {
        // Normalize strings
        $proyectoNumero = trim($proyectoNumero);
        $cliente = trim($cliente);
        $descripcionProyecto = trim($descripcionProyecto);

        $data = $ano . '|' . $proyectoNumero . '|' . $cliente . '|' . $descripcionProyecto;
        
        return hash('sha256', $data);
    }

    /**
     * Check if a record with the same hash exists.
     */
    public static function existsByHash(string $hash): bool
    {
        return self::where('hash', $hash)->exists();
    }
}
