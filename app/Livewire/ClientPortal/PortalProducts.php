<?php

namespace App\Livewire\ClientPortal;

use Livewire\Component;

class PortalProducts extends Component
{
    public function render()
    {
        $client = \App\Models\Client::where('user_id', auth()->id())->firstOrFail();

        $orders = \App\Models\Order::query()->with('product')->where('client_id', $client->id)
            ->latest()
            ->get();

        return view('livewire.client-portal.portal-products', [
            'orders' => $orders,
            'client' => $client
        ])->layout('layouts.app');
    }
}
