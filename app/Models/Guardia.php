<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Guardia extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name'];

    public function hourDetails()
    {
        return $this->hasMany(HourDetail::class);
    }
}
