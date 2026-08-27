<?php

namespace App\Livewire\Payments;

use App\Models\PaymentMethod;
use Illuminate\Support\Str;
use Livewire\Component;

class PaymentMethodIndex extends Component
{
    public $newLabel = '';
    public $newRequiresConfirmation = true;

    public function toggleConfirmation(int $id)
    {
        $method = PaymentMethod::findOrFail($id);
        $method->requires_confirmation = !$method->requires_confirmation;
        $method->save();
    }

    public function toggleActive(int $id)
    {
        $method = PaymentMethod::findOrFail($id);
        $method->is_active = !$method->is_active;
        $method->save();
    }

    public function addMethod()
    {
        $this->validate([
            'newLabel' => 'required|string|max:255',
            'newRequiresConfirmation' => 'boolean',
        ]);

        $key = Str::slug($this->newLabel, '_');

        if (PaymentMethod::where('key', $key)->exists()) {
            $this->addError('newLabel', __('A payment method with this name already exists.'));
            return;
        }

        PaymentMethod::create([
            'key' => $key,
            'label' => $this->newLabel,
            'requires_confirmation' => $this->newRequiresConfirmation,
            'is_active' => true,
            'sort_order' => (PaymentMethod::max('sort_order') ?? 0) + 10,
        ]);

        $this->newLabel = '';
        $this->newRequiresConfirmation = true;
        session()->flash('message', __('Payment method added.'));
    }

    public function render()
    {
        return view('livewire.payments.payment-method-index', [
            'methods' => PaymentMethod::orderBy('sort_order')->get(),
        ])->layout('layouts.app');
    }
}
