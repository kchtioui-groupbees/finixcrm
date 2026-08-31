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
        'cashback_note', 'cashback_expires_at',
        // Renewal fields (snapshot of the product's defaults, overridable per order)
        'renewable', 'renewal_interval_unit', 'renewal_interval_value',
        'renewal_price', 'next_due_date',
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
        'renewable'                => 'boolean',
        'renewal_interval_value'   => 'integer',
        'renewal_price'            => 'decimal:2',
        'next_due_date'            => 'date',
        'cashback_expires_at'      => 'date',
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

    /**
     * The 5-state French cashback lifecycle: en_attente, disponible, utilise,
     * annule, expire. Returns null for an order with no cashback at all
     * (getCashbackStatusAttribute()'s "not_eligible" — no badge needed).
     *
     * "utilise" is a FIFO approximation: the ledger tracks total credit
     * spent per client but not which specific cashback grant it came from,
     * so a client's own cashback grants are treated as consumed oldest
     * first (the same FIFO principle PaymentAllocationService already uses
     * for payments), and this order's grant is "utilise" once the running
     * total of the client's spent credit has reached it.
     */
    public function getCashbackStatusLabelAttribute(): ?string
    {
        if (!$this->cashback_enabled_snapshot || (float) $this->cashback_amount <= 0) {
            return null;
        }

        if ($this->cashback_reversed) {
            return 'annule';
        }

        if (!$this->cashback_rewarded) {
            return 'en_attente';
        }

        $clientUsed = (float) $this->client->cashback_used;

        $priorAndOwnRewardedSum = (float) $this->client->orders()
            ->where('cashback_rewarded', true)
            ->where(function ($q) {
                $q->where('cashback_rewarded_at', '<', $this->cashback_rewarded_at)
                    ->orWhere(function ($q2) {
                        $q2->where('cashback_rewarded_at', $this->cashback_rewarded_at)
                            ->where('id', '<=', $this->id);
                    });
            })
            ->sum('cashback_amount');

        if ($clientUsed >= $priorAndOwnRewardedSum - 0.0001) {
            return 'utilise';
        }

        if ($this->cashback_expires_at && $this->cashback_expires_at->isPast()) {
            return 'expire';
        }

        return 'disponible';
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

    public function getWarrantyDaysRemainingAttribute(): ?int
    {
        if (!$this->warranty_enabled || !$this->warranty_end_date) {
            return null;
        }

        return (int) now()->startOfDay()->diffInDays($this->warranty_end_date, false);
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

    // ── Renewal scopes ───────────────────────────────────────────────────

    public function scopeRenewable($query)
    {
        return $query->where('renewable', true)->whereNotNull('next_due_date');
    }

    public function scopeDueWithin($query, int $days)
    {
        return $query->renewable()
            ->whereDate('next_due_date', '>=', today())
            ->whereDate('next_due_date', '<=', today()->addDays($days));
    }

    public function scopeDueToday($query)
    {
        return $query->renewable()->whereDate('next_due_date', today());
    }

    public function scopeOverdueRenewals($query)
    {
        return $query->renewable()->whereDate('next_due_date', '<', today());
    }

    public function getIsOverdueRenewalAttribute(): bool
    {
        return (bool) $this->renewable
            && (bool) $this->next_due_date
            && $this->next_due_date->isPast()
            && !$this->next_due_date->isToday();
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
