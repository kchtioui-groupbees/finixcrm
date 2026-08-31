<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name', 'slug', 'description', 'category', 'is_active',
        'warranty_enabled', 'warranty_duration_days', 'warranty_type', 'warranty_terms',
        'cashback_enabled', 'cashback_type', 'cashback_value',
        'renewable', 'renewal_interval_unit', 'renewal_interval_value', 'default_renewal_price',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'warranty_enabled' => 'boolean',
        'cashback_enabled' => 'boolean',
        'cashback_value' => 'decimal:3',
        'renewable' => 'boolean',
        'renewal_interval_value' => 'integer',
        'default_renewal_price' => 'decimal:3',
    ];

    public function fields()
    {
        return $this->hasMany(ProductField::class)->orderBy('sort_order', 'asc');
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
