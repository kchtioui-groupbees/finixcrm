<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentAllocation extends Model
{
    protected $fillable = [
        'payment_id',
        'balance_transaction_id',
        'order_id',
        'amount',
    ];

    protected $casts = [
        'amount' => 'decimal:3',
    ];

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    public function balanceTransaction()
    {
        return $this->belongsTo(ClientBalanceTransaction::class, 'balance_transaction_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
