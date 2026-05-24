<?php
// app/Models/Inventory.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    protected $fillable = [
        'document_number',
        'document_date',
        'warehouse_id',
        'user_id',
        'status',
        'comment',
    ];

    protected $dates = ['document_date'];

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(InventoryItem::class);
    }

    public function isDraft()
    {
        return $this->status === 'draft';
    }

    public function isApplied()
    {
        return $this->status === 'applied';
    }

    public function isCancelled()
    {
        return $this->status === 'cancelled';
    }
}
