<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class GoodsReceiptItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'goods_receipt_id',
        'product_id',
        'barcode',
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

    public function receipt()
    {
        return $this->belongsTo(GoodsReceipt::class, 'goods_receipt_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
