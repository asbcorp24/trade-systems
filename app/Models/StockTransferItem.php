<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockTransferItem extends Model
{
    protected $fillable = [
        'stock_transfer_id',
        'product_id',
        'quantity',
        'received_quantity',
        'unit_price',
        'expiry_date',
        'batch',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'received_quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'expiry_date' => 'date',
    ];

    public function transfer()
    {
        return $this->belongsTo(StockTransfer::class, 'stock_transfer_id');
    }
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
