<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    protected $fillable = [
        'product_id',
        'warehouse_id',
        'store_id',
        'document_type',
        'document_id',
        'direction',
        'quantity',
        'unit_price',
        'is_used',
        'expiry_date',
        'batch',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'is_used' => 'boolean',
        'expiry_date' => 'date',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}
