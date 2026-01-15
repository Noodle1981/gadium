<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BoardDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'ano',
        'proyecto_numero',
        'cliente',
        'descripcion_proyecto',
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
