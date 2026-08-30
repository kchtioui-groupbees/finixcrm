<?php

namespace App\Livewire\ClientPortal;

use App\Models\PaymentMethod;
use Livewire\Component;

class PaymentMethods extends Component
{
    public function render()
    {
        $methods = PaymentMethod::publicReady()->groupBy('category');

        return view('livewire.client-portal.payment-methods', [
            'methodsByCategory' => $methods,
            'categoryLabels' => PaymentMethod::CATEGORIES,
        ])->layout('layouts.app');
    }
}
