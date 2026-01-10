<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    protected $fillable = [
        'nombre',
        'nombre_normalizado',
    ];

    /**
     * Relación con proyectos
     */
    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    /**
     * Relación con ventas
     */
    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    /**
     * Relación con aliases
     */
    public function aliases(): HasMany
    {
        return $this->hasMany(ClientAlias::class);
    }

    /**
     * Normalizar nombre de cliente
     * Convierte a minúsculas, elimina espacios extras y caracteres especiales
     */
    public static function normalizeClientName(string $nombre): string
    {
        // Convertir a minúsculas
        $normalized = mb_strtolower($nombre, 'UTF-8');
        
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

        static::saving(function ($client) {
            $client->nombre_normalizado = self::normalizeClientName($client->nombre);
        });
    }
}

