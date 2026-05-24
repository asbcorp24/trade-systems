<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockTransfer extends Model
{
    protected $fillable = [
        'from_warehouse_id',
        'to_warehouse_id',
        'from_store_id',
        'to_store_id',
        'user_id',
        'document_number',
        'document_date',
        'comment',
    ];
    protected $casts = [
        'document_date' => 'datetime',
    ];
    public function items()
    {
        return $this->hasMany(StockTransferItem::class);
    }
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }
    public function fromWarehouse() { return $this->belongsTo(Warehouse::class, 'from_warehouse_id'); }
    public function toWarehouse() { return $this->belongsTo(Warehouse::class, 'to_warehouse_id'); }

    public function fromStore() { return $this->belongsTo(Store::class, 'from_store_id'); }
    public function toStore() { return $this->belongsTo(Store::class, 'to_store_id'); }
    public function isDraft()
    {
        return $this->status === 'draft';
    }

    public function isPosted()
    {
        return $this->status === 'posted';
    }

    public function isCanceled()
    {
        return $this->status === 'canceled';
    }

}
