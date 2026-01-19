<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplierAlias extends Model
{
    protected $fillable = [
        'supplier_id',
        'alias',
    ];

    /**
     * Relación con el proveedor principal
     */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
}
