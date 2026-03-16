<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'client_id', 'product_id', 'price', 'purchase_date',
        'expiry_date', 'duration', 'status', 'reminder_date', 'internal_note',
        'warranty_enabled', 'warranty_duration_days', 'warranty_start_mode',
        'warranty_start_date', 'warranty_end_date', 'warranty_terms_snapshot',
        'currency', 'cashback_rewarded',
        // Cashback snapshot fields (set at order creation, never change)
        'cashback_enabled_snapshot', 'cashback_type_snapshot',
        'cashback_value_snapshot', 'cashback_amount',
        'cashback_rewarded_at', 'cashback_reversed',
    ];

    protected $casts = [
        'purchase_date'            => 'date',
        'expiry_date'              => 'date',
        'reminder_date'            => 'date',
        'warranty_start_date'      => 'date',
        'warranty_end_date'        => 'date',
        'cashback_rewarded_at'     => 'datetime',
        'warranty_enabled'         => 'boolean',
        'cashback_rewarded'        => 'boolean',
        'cashback_enabled_snapshot'=> 'boolean',
        'cashback_reversed'        => 'boolean',
        'cashback_amount'          => 'decimal:3',
        'cashback_value_snapshot'  => 'decimal:3',
        'price'                    => 'decimal:3',
    ];

    // ── Relationships ────────────────────────────────────────────────────

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function fieldValues()
    {
        return $this->hasMany(OrderFieldValue::class);
    }

    public function allocations()
    {
        return $this->hasMany(PaymentAllocation::class);
    }

    public function warrantyClaims()
    {
        return $this->hasMany(WarrantyClaim::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    // ── Computed attributes ──────────────────────────────────────────────

    public function getPaidAmountAttribute(): float
    {
        return (float) $this->allocations()
            ->where(function ($query) {
                $query->whereHas('payment', function ($q) {
                    $q->where('status', 'completed');
                })->orWhereNotNull('balance_transaction_id');
            })
            ->sum('amount');
    }

    public function getPendingAmountAttribute(): float
    {
        $pending = (float) $this->price - $this->paid_amount;
        return $pending > 0 ? $pending : 0.0;
    }

    public function getPaymentStatusAttribute(): string
    {
        $paid  = $this->paid_amount;
        $total = (float) $this->price;

        if ($paid <= 0)      return 'unpaid';
        if ($paid < $total)  return 'partially_paid';
        return 'paid';
    }

    // ── Cashback helpers (for UI / admin display) ────────────────────────

    /**
     * Returns one of: not_eligible | pending_reward | rewarded | reversed
     */
    public function getCashbackStatusAttribute(): string
    {
        if (!$this->cashback_enabled_snapshot || (float) $this->cashback_amount <= 0) {
            return 'not_eligible';
        }
        if ($this->cashback_reversed) {
            return 'reversed';
        }
        if ($this->cashback_rewarded) {
            return 'rewarded';
        }
        return 'pending_reward';
    }

    // ── Warranty helpers ─────────────────────────────────────────────────

    public function getWarrantyStatusAttribute(): string
    {
        if (!$this->warranty_enabled) return 'No Warranty';
        if (!$this->warranty_end_date) return 'Pending Activation';

        $warningDate = now()->addDays(15)->startOfDay();

        if ($this->warranty_end_date->isPast())           return 'Warranty Expired';
        if ($this->warranty_end_date <= $warningDate)     return 'Warranty Expiring Soon';
        return 'Under Warranty';
    }

    // ── Dynamic status ───────────────────────────────────────────────────

    public function getDynamicStatusAttribute(): ?string
    {
        if ($this->pending_amount > 0) return 'Payment Pending';

        $warningDate = now()->addDays(7)->startOfDay();
        if ($this->expiry_date) {
            if ($this->expiry_date->isPast())          return 'Expired';
            if ($this->expiry_date <= $warningDate)    return 'Expiring Soon';
        }

        return null;
    }

    // ── Currency formatting ──────────────────────────────────────────────

    public function formatAmount($amount): string
    {
        $decimals = ($this->currency === 'TND') ? 3 : 2;
        $symbol   = match ($this->currency) {
            'USD'   => '$',
            'EUR'   => '€',
            'TND'   => 'TND ',
            default => $this->currency . ' ',
        };
        return $symbol . number_format($amount, $decimals);
    }

    // ── Scopes ───────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('expiry_date', '>', now());
    }

    public function scopeExpiringSoon($query)
    {
        return $query->where('expiry_date', '>', now())
                     ->where('expiry_date', '<=', now()->addDays(30));
    }

    public function scopeExpired($query)
    {
        return $query->where('expiry_date', '<=', now());
    }

    // ── Legacy compatibility shim ────────────────────────────────────────

    /**
     * @deprecated Use App\Services\CashbackRewardService::rewardIfEligible() instead.
     *             Kept for backwards compatibility with any code still calling this method.
     */
    public function checkAndApplyCashback(): bool
    {
        return app(\App\Services\CashbackRewardService::class)->rewardIfEligible($this);
    }
}
