<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductField extends Model
{
    protected $fillable = [
        'product_id', 'label', 'name', 'type', 'placeholder', 
        'is_required', 'is_client_visible', 'is_admin_only', 
        'options_json', 'default_value', 'sort_order'
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'is_client_visible' => 'boolean',
        'is_admin_only' => 'boolean',
        'options_json' => 'array',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
