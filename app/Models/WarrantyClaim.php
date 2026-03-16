<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WarrantyClaim extends Model
{
    protected $fillable = [
        'order_id', 'subject', 'description', 'status', 'admin_notes'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
