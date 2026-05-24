<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;
    protected $fillable = [
        'barcode',
        'name',
        'unit',
        'photo_path',
        'category_id',
        'base_price',
        'description','min_stock', 'max_stock',
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
    ];
    public function unitRef()
    {
        return $this->belongsTo(Unit::class, 'unit', 'code');
    }
    // Категория
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // Движения по складу (регист накопления)
    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    // строки приходов
    public function receiptItems()
    {
        return $this->hasMany(GoodsReceiptItem::class);
    }

    // строки перемещений
    public function transferItems()
    {
        return $this->hasMany(StockTransferItem::class);
    }
    public function attributes()
    {
        return $this->hasMany(ProductAttribute::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }
    public function priceHistories()
    {
        return $this->hasMany(PriceHistory::class);
    }

}
