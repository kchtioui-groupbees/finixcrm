<?php

namespace App\Livewire\Payments;

use App\Models\PaymentMethod;
use Illuminate\Support\Str;
use Livewire\Component;

class PaymentMethodIndex extends Component
{
    public $newLabel = '';
    public $newRequiresConfirmation = true;

    // Details edit modal (bank_transfer / postal_transfer / crypto only —
    // the fields an admin must fill in themselves, never invented by seeding)
    public $editingId = null;
    public $editHolder = '';
    public $editRib = '';
    public $editBankName = '';
    public $editRibPostal = '';
    public $editWalletAddress = '';

    public function toggleConfirmation(int $id)
    {
        $method = PaymentMethod::findOrFail($id);
        $method->requires_confirmation = !$method->requires_confirmation;
        $method->save();
    }

    public function toggleActive(int $id)
    {
        $method = PaymentMethod::findOrFail($id);
        $activating = !$method->is_active;

        if ($activating && $method->category === 'crypto' && empty($method->details['wallet_address'] ?? null)) {
            session()->flash('error', __('Configure a wallet address for :label before activating it.', ['label' => $method->label]));
            return;
        }

        $method->is_active = $activating;
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

    public function openEdit(int $id)
    {
        $method = PaymentMethod::findOrFail($id);
        $details = $method->details ?? [];

        $this->editingId = $method->id;
        $this->editHolder = $details['holder'] ?? '';
        $this->editRib = $details['rib'] ?? '';
        $this->editBankName = $details['bank_name'] ?? '';
        $this->editRibPostal = $details['rib_postal'] ?? '';
        $this->editWalletAddress = $details['wallet_address'] ?? '';
    }

    public function closeEdit()
    {
        $this->editingId = null;
    }

    public function saveDetails()
    {
        $method = PaymentMethod::findOrFail($this->editingId);
        $details = $method->details ?? [];

        $details = match ($method->category) {
            'bank_transfer' => [
                'holder' => $this->editHolder ?: null,
                'rib' => $this->editRib ?: null,
                'bank_name' => $this->editBankName ?: null,
            ],
            'postal_transfer' => [
                'holder' => $this->editHolder ?: null,
                'rib_postal' => $this->editRibPostal ?: null,
            ],
            'crypto' => array_merge($details, [
                'wallet_address' => $this->editWalletAddress ?: null,
            ]),
            default => $details,
        };

        $method->details = $details;
        $method->save();

        $this->editingId = null;
        session()->flash('message', __('Payment method details updated.'));
    }

    public function render()
    {
        return view('livewire.payments.payment-method-index', [
            'methods' => PaymentMethod::orderBy('sort_order')->get(),
        ])->layout('layouts.app');
    }
}
