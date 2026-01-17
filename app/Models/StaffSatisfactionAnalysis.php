<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaffSatisfactionAnalysis extends Model
{
    use HasFactory;

    protected $table = 'staff_satisfaction_analysis';

    protected $fillable = [
        'periodo',
        'p1_mal_count', 'p1_normal_count', 'p1_bien_count',
        'p1_mal_pct', 'p1_normal_pct', 'p1_bien_pct',
        'p2_mal_count', 'p2_normal_count', 'p2_bien_count',
        'p2_mal_pct', 'p2_normal_pct', 'p2_bien_pct',
        'p3_mal_count', 'p3_normal_count', 'p3_bien_count',
        'p3_mal_pct', 'p3_normal_pct', 'p3_bien_pct',
        'p4_mal_count', 'p4_normal_count', 'p4_bien_count',
        'p4_mal_pct', 'p4_normal_pct', 'p4_bien_pct',
        'total_respuestas'
    ];
}
