<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class WeightingFactor extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'role_name',
        'value',
        'start_date',
        'end_date',
        'is_active',
    ];

    protected $casts = [
        'value' => 'decimal:8',
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * Scope para obtener el factor vigente para un rol en una fecha específica.
     */
    public function scopeVigente(Builder $query, string $roleName, $date = null): Builder
    {
        $date = $date ?: now()->toDateString();

        return $query->where('role_name', $roleName)
            ->where('is_active', true)
            ->where('start_date', '<=', $date)
            ->where(function (Builder $query) use ($date) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', $date);
            })
            ->orderByDesc('start_date');
    }
}
