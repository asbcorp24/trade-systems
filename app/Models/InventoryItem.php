<?php
// app/Models/InventoryItem.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryItem extends Model
{
    protected $fillable = [
        'inventory_id',
        'product_id',
        'expected_qty',
        'actual_qty',
        'diff_qty',
        'unit_price',
        'diff_value',
    ];

    protected $casts = [
        'expected_qty' => 'integer',
        'actual_qty' => 'integer',
        'diff_qty' => 'integer',
        'unit_price' => 'decimal:2',
        'diff_value' => 'decimal:2',
    ];

    public function inventory()
    {
        return $this->belongsTo(Inventory::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
