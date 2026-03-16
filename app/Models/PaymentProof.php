<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentProof extends Model
{
    protected $fillable = [
        'payment_id', 'file_path', 'file_type', 'original_name'
    ];

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }
}
