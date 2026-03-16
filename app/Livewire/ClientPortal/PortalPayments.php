<?php

namespace App\Livewire\ClientPortal;

use Livewire\Component;

class PortalPayments extends Component
{
    public function render()
    {
        $client = \App\Models\Client::where('user_id', auth()->id())->first();

        $payments = [];
        $total_paid = 0;
        if ($client) {
            $payments = \App\Models\Payment::where('client_id', '=', $client->id)
                ->with(['order.product', 'allocations.order.product', 'proofs'])
                ->orderBy('payment_date', 'desc')
                ->get();
            
            $total_paid = $payments->where('status', 'completed')->sum('amount');
        }

        return view('livewire.client-portal.portal-payments', [
            'payments' => $payments,
            'total_paid' => $total_paid,
            'client' => $client
        ])->layout('layouts.app');
    }
}
