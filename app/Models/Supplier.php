<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'tax_id',
        'address',
        'email',
        'phone',
        'status',
    ];

    /**
     * Relación con detalles de compras
     */
    public function purchaseDetails()
    {
        return $this->hasMany(PurchaseDetail::class);
    }
}
