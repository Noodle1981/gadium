<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AutomationProject extends Model
{
    protected $fillable = [
        'proyecto_id',
        'cliente',
        'proyecto_descripcion',
        'fat',
        'pem',
        'hash',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Generate a unique hash for duplicate detection
     */
    public static function generateHash(array $data): string
    {
        $hashData = [
            'proyecto_id' => trim($data['proyecto_id'] ?? ''),
            'cliente' => trim($data['cliente'] ?? ''),
            'proyecto_descripcion' => trim($data['proyecto_descripcion'] ?? ''),
        ];
        
        return hash('sha256', json_encode($hashData));
    }

    /**
     * Scope to find by hash
     */
    public function scopeByHash($query, string $hash)
    {
        return $query->where('hash', $hash);
    }
}
