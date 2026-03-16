<?php

namespace App\Livewire\ClientPortal;

use Livewire\Component;

class PortalProductShow extends Component
{
    public $order;
    public $subject = '';
    public $description = '';
    public $showClaimForm = false;

    protected $rules = [
        'subject' => 'required|string|min:5|max:255',
        'description' => 'required|string|min:20',
    ];

    public function toggleClaimForm()
    {
        $this->showClaimForm = !$this->showClaimForm;
    }

    public function submitClaim()
    {
        $this->validate();

        $this->order->warrantyClaims()->create([
            'subject' => $this->subject,
            'description' => $this->description,
            'status' => 'open',
        ]);

        $this->reset(['subject', 'description', 'showClaimForm']);
        
        // Refresh the order to show the new claim
        $this->mount($this->order->id);

        session()->flash('message', 'Your warranty claim has been submitted successfully.');
    }

    public function mount($order)
    {
        $client = \App\Models\Client::where('user_id', '=', auth()->id())->firstOrFail();

        // Securely load the order ensuring it belongs to THIS client
        $this->order = \App\Models\Order::where('client_id', '=', $client->id)
            ->with([
                'product', 
                'fieldValues.field', 
                'payments' => function($query) {
                    $query->latest('payment_date');
                }, 
                'payments.proofs',
                'warrantyClaims' => function($q) {
                    $q->latest();
                },
                'allocations.balanceTransaction'
            ])
            ->findOrFail($order);
    }

    public function render()
    {
        return view('livewire.client-portal.portal-product-show')->layout('layouts.app');
    }
}
