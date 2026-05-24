<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransferDiscrepancy extends Model
{
    protected $fillable = [
        'stock_transfer_id',
        'product_id',
        'shipped_quantity',
        'received_quantity',
        'shortage_quantity',
        'surplus_quantity',
        'user_id',
        'comment',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
