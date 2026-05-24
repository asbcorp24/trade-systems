<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attribute extends Model
{

    protected $fillable = ['name', 'type'];


    public function productAttributes()
    {
        return $this->hasMany(ProductAttribute::class);
    }
}
