<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use OwenIt\Auditing\Contracts\Auditable;

class StaffSatisfactionResponse extends Model implements Auditable
{
    use HasFactory, \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'personal',
        'fecha',
        'p1_mal', 'p1_normal', 'p1_bien',
        'p2_mal', 'p2_normal', 'p2_bien',
        'p3_mal', 'p3_normal', 'p3_bien',
        'p4_mal', 'p4_normal', 'p4_bien',
        'hash'
    ];

    protected $casts = [
        'fecha' => 'date',
        'p1_mal' => 'boolean', 'p1_normal' => 'boolean', 'p1_bien' => 'boolean',
        'p2_mal' => 'boolean', 'p2_normal' => 'boolean', 'p2_bien' => 'boolean',
        'p3_mal' => 'boolean', 'p3_normal' => 'boolean', 'p3_bien' => 'boolean',
        'p4_mal' => 'boolean', 'p4_normal' => 'boolean', 'p4_bien' => 'boolean',
    ];

    public static function generateHash(array $row): string
    {
        // Concatenar todos los valores relevantes para crear un hash único
        $dataStr = $row['personal'] . '|' . 
                   ($row['p1_mal'] ? '1' : '0') . ($row['p1_normal'] ? '1' : '0') . ($row['p1_bien'] ? '1' : '0') . '|' .
                   ($row['p2_mal'] ? '1' : '0') . ($row['p2_normal'] ? '1' : '0') . ($row['p2_bien'] ? '1' : '0') . '|' .
                   ($row['p3_mal'] ? '1' : '0') . ($row['p3_normal'] ? '1' : '0') . ($row['p3_bien'] ? '1' : '0') . '|' .
                   ($row['p4_mal'] ? '1' : '0') . ($row['p4_normal'] ? '1' : '0') . ($row['p4_bien'] ? '1' : '0');
                   
        // Si hay fecha, agregarla (para distinguir misma respuesta en meses distintos)
        if (isset($row['fecha'])) {
             $dataStr .= '|' . (is_string($row['fecha']) ? $row['fecha'] : $row['fecha']->format('Y-m-d'));
        }

        return hash('sha256', $dataStr);
    }

    public static function existsByHash(string $hash): bool
    {
        return self::where('hash', $hash)->exists();
    }
}
