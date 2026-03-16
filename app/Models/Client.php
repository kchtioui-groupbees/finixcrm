<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $fillable = [
        'name', 'email', 'phone', 'notes', 'tags', 'user_id', 'credit_balance', 'currency'
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
