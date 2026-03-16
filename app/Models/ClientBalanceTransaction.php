<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientBalanceTransaction extends Model
{
    protected $fillable = [
        'client_id',
        'amount',
        'type',
        'payment_id',
        'description',
        'currency',
        'reference_type',
        'reference_id',
    ];

    // ── Relationships ────────────────────────────────────────────────────

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    public function allocations()
    {
        return $this->hasMany(PaymentAllocation::class, 'balance_transaction_id');
    }

    /**
     * Polymorphic-style helper: get the referenced model (e.g. Order).
     * Not a real Laravel morph to keep schema simple.
     */
    public function referencedModel(): ?Model
    {
        if (!$this->reference_type || !$this->reference_id) {
            return null;
        }
        $map = [
            'order' => Order::class,
        ];
        $class = $map[$this->reference_type] ?? null;
        return $class ? $class::find($this->reference_id) : null;
    }

    // ── Scopes ───────────────────────────────────────────────────────────

    public function scopeCashbackRewards($query)
    {
        return $query->where('type', 'cashback_reward');
    }

    public function scopeForOrder($query, int $orderId)
    {
        return $query->where('reference_type', 'order')
                     ->where('reference_id', $orderId);
    }
}
