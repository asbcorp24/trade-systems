<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $fillable = [
        'user_id',
        'event',
        'auditable_type',
        'auditable_id',
        'description',
        'properties',
    ];

    protected $casts = [
        'properties' => 'array',
    ];
}
