<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentMethodField extends Model
{
    public const TYPES = ['text', 'phone', 'email', 'link', 'wallet_address'];

    protected $fillable = [
        'payment_method_id', 'label', 'value', 'type', 'is_public', 'copyable', 'sort_order',
    ];

    protected $casts = [
        'is_public' => 'boolean',
        'copyable' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    public function scopeConfigured($query)
    {
        return $query->whereNotNull('value')->where('value', '!=', '');
    }
}
