<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $fillable = [
        'name', 'email', 'finix_email', 'status', 'phone', 'notes', 'tags', 'user_id', 'credit_balance', 'currency'
    ];

    protected $casts = [
        'tags' => 'array',
        'credit_balance' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function reminders()
    {
        return $this->hasMany(Reminder::class);
    }

    public function balanceTransactions()
    {
        return $this->hasMany(ClientBalanceTransaction::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function warrantyClaims()
    {
        return $this->hasManyThrough(WarrantyClaim::class, Order::class);
    }

    public function getTotalPaidAttribute()
    {
        return $this->payments()->where('status', 'completed')->sum('amount');
    }

    public function getTotalPendingAttribute()
    {
        return $this->orders->sum(fn($order) => $order->pending_amount);
    }

    public function getActiveOrdersCountAttribute()
    {
        return $this->orders()->whereIn('status', ['active', 'completed'])->count();
    }

    public function getExpiredOrdersCountAttribute()
    {
        return $this->orders()->where('status', 'expired')->count();
    }

    public function getTotalOrdersCountAttribute()
    {
        return $this->orders()->count();
    }

    public function getTotalOrdersAmountAttribute()
    {
        return (float) $this->orders()->sum('price');
    }

    public function getWarrantyActiveCountAttribute()
    {
        return $this->orders()
            ->where('warranty_enabled', true)
            ->whereDate('warranty_end_date', '>=', now())
            ->count();
    }

    public function getWarrantyExpiredCountAttribute()
    {
        return $this->orders()
            ->where('warranty_enabled', true)
            ->whereDate('warranty_end_date', '<', now())
            ->count();
    }

    /**
     * A wa.me link for this client's phone. Tunisian numbers are stored
     * locally without the country code, so an 8-digit number gets '216'
     * prepended; numbers already carrying a country code pass through.
     */
    public function getWhatsappUrlAttribute(): ?string
    {
        if (!$this->phone) {
            return null;
        }

        $digits = preg_replace('/\D/', '', $this->phone);

        if (strlen($digits) <= 8) {
            $digits = '216' . $digits;
        }

        return "https://wa.me/{$digits}";
    }

    /**
     * Total cashback ever earned by this client (ledger 'cashback_reward'
     * entries), regardless of whether it has since been used.
     */
    public function getCashbackEarnedAttribute(): float
    {
        return (float) $this->balanceTransactions()
            ->where('type', 'cashback_reward')
            ->sum('amount');
    }

    /**
     * Approximate cashback "used" amount.
     *
     * The ledger does not track which credit *source* (cashback vs.
     * overpayment) a later 'usage' debit actually drew from — it is one
     * shared pool. As a safe, clearly-labelled approximation, cashback is
     * considered used up to the smaller of (total cashback earned, total
     * credit ever spent). This never overstates how much cashback remains
     * available.
     */
    public function getCashbackUsedAttribute(): float
    {
        $totalUsed = (float) $this->balanceTransactions()
            ->where('type', 'usage')
            ->sum('amount'); // stored as negative

        return min($this->cashback_earned, abs($totalUsed));
    }

    public function getCashbackAvailableAttribute(): float
    {
        return max(0.0, $this->cashback_earned - $this->cashback_used);
    }

    /**
     * Best-effort "last activity": the most recent of account login, order,
     * or payment. Null if the client has never done anything yet.
     */
    public function getLastActivityAtAttribute(): ?\Carbon\Carbon
    {
        $dates = collect([
            $this->user?->last_login_at,
            $this->orders()->max('created_at'),
            $this->payments()->max('created_at'),
        ])->filter()->map(fn ($d) => \Carbon\Carbon::parse($d));

        return $dates->isEmpty() ? null : $dates->max();
    }

    /**
     * Recalculate and update the cached credit balance
     */
    public function refreshBalance()
    {
        $this->credit_balance = $this->balanceTransactions()->sum('amount');
        $this->save();
        return $this->credit_balance;
    }

    public function formatAmount($amount)
    {
        $decimals = ($this->currency === 'TND') ? 3 : 2;
        $symbol = match($this->currency) {
            'USD' => '$',
            'EUR' => '€',
            'TND' => 'TND ',
            default => $this->currency . ' '
        };

        return $symbol . number_format($amount, $decimals);
    }

    public function getCurrencySymbolAttribute()
    {
        return match($this->currency) {
            'USD' => '$',
            'EUR' => '€',
            'TND' => 'TND',
            default => $this->currency
        };
    }
}
