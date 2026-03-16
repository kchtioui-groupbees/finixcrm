<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderFieldValue extends Model
{
    protected $fillable = [
        'order_id', 'product_field_id', 'value'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function field()
    {
        return $this->belongsTo(ProductField::class, 'product_field_id');
    }
}
