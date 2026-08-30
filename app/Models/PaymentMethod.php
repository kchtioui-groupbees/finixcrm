<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    protected $fillable = [
        'key', 'label', 'category', 'currencies', 'requires_confirmation', 'is_active', 'sort_order',
        'fee_type', 'fee_value', 'fee_paid_by', 'fee_label', 'details',
    ];

    protected $casts = [
        'requires_confirmation' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'currencies' => 'array',
        'fee_value' => 'decimal:3',
        'details' => 'array',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    /**
     * Human-readable fee summary. Never renders "0" for a genuinely unknown
     * fee — the fee_label (e.g. "Les éventuels frais de paiement sont à la
     * charge du client.") is shown instead.
     */
    public function getFeeSummaryAttribute(): string
    {
        return match ($this->fee_type) {
            'percentage' => rtrim(rtrim((string) $this->fee_value, '0'), '.') . '%',
            'fixed' => $this->fee_value !== null
                ? rtrim(rtrim((string) $this->fee_value, '0'), '.') . ' ' . ($this->currencies[0] ?? '')
                : '—',
            default => $this->fee_label ?? __('Fee unknown — charged to the customer if any applies.'),
        };
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
