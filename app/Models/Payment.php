<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'client_id', 'order_id', 'amount', 'payment_method', 'status', 'payment_date', 'type', 'internal_notes', 'currency'
    ];

    protected $casts = [
        'payment_date' => 'date',
    ];

    protected static function booted()
    {
        static::deleting(function ($payment) {
            // Store references before deletion
            $payment->_client_id_cache = $payment->client_id;
        });

        static::deleted(function ($payment) {
            if (isset($payment->_client_id_cache)) {
                // Reallocate everything now that this payment is gone
                app(\App\Services\PaymentAllocationService::class)
                    ->reallocateForClient((int) $payment->_client_id_cache);
            }
        });
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function allocations()
    {
        return $this->hasMany(PaymentAllocation::class);
    }

    public function proofs()
    {
        return $this->hasMany(PaymentProof::class);
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
}
