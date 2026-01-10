<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyMetricsAggregate extends Model
{
    protected $fillable = [
        'metric_date',
        'metric_type',
        'metric_data',
    ];

    protected $casts = [
        'metric_data' => 'array',
        'metric_date' => 'date',
    ];
}
