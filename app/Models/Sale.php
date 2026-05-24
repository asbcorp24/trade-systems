<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $fillable = [
        'store_id', 'user_id', 'document_number', 'document_date',
        'total_amount', 'discount_percent', 'customer_name', 'customer_phone',
        'comment', 'is_refund', 'is_refunded'
    ];


    protected $casts = [
        'document_date' => 'datetime',
        'total_amount'  => 'decimal:2',
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
