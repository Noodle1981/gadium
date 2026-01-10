<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ManufacturingLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'user_id',
        'units_produced',
        'correction_documents',
        'hours_clock',
        'hours_weighted',
        'recorded_at',
    ];

    protected $casts = [
        'recorded_at' => 'datetime',
        'hours_clock' => 'decimal:2',
        'hours_weighted' => 'decimal:2',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
