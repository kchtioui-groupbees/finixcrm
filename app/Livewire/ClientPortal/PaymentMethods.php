<?php

namespace App\Livewire\ClientPortal;

use Livewire\Component;

class PaymentMethods extends Component
{
    public function render()
    {
        return view('livewire.client-portal.payment-methods')
            ->layout('layouts.app');
    }
}
