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

    public function inventory()
    {
        return $this->belongsTo(Inventory::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
