<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    protected $fillable = [
        'key', 'label', 'requires_confirmation', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'requires_confirmation' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    /**
     * Whether a payment logged under this method key needs manual
     * confirmation before it has any financial effect. Defaults to true
     * (fail-safe) for a key with no matching configuration row.
     */
    public static function requiresConfirmation(?string $key): bool
    {
        if (!$key) {
            return true;
        }

        $value = static::where('key', $key)->value('requires_confirmation');

        return $value === null ? true : (bool) $value;
    }
}
