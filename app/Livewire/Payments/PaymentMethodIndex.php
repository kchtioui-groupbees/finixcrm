<?php

namespace App\Livewire\Payments;

use App\Models\PaymentMethod;
use Livewire\Component;

class PaymentMethodIndex extends Component
{
    public $showArchived = false;

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

        if ($activating && $method->category === 'crypto' && !$method->fields()->public()->configured()->where('type', 'wallet_address')->exists()) {
            session()->flash('error', __('Configure a wallet address for :label before activating it.', ['label' => $method->label]));
            return;
        }

        $method->is_active = $activating;
        $method->save();
    }

    public function duplicate(int $id)
    {
        $method = PaymentMethod::findOrFail($id);
        $clone = $method->duplicate();

        session()->flash('message', __(':label duplicated as :new (inactive).', ['label' => $method->label, 'new' => $clone->label]));
    }

    public function archive(int $id)
    {
        $method = PaymentMethod::findOrFail($id);
        $method->archived_at = now();
        $method->is_active = false;
        $method->save();

        session()->flash('message', __('Payment method archived.'));
    }

    public function unarchive(int $id)
    {
        $method = PaymentMethod::findOrFail($id);
        $method->archived_at = null;
        $method->save();

        session()->flash('message', __('Payment method restored.'));
    }

    public function render()
    {
        $methods = PaymentMethod::query()
            ->when(!$this->showArchived, fn ($q) => $q->notArchived())
            ->when($this->showArchived, fn ($q) => $q->whereNotNull('archived_at'))
            ->orderBy('sort_order')
            ->get();

        return view('livewire.payments.payment-method-index', [
            'methods' => $methods,
        ])->layout('layouts.app');
    }
}
