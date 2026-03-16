<?php

namespace App\Livewire\Payments;

use Livewire\Component;

class PaymentIndex extends Component
{
    use \Livewire\WithPagination;

    public $search = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function deletePayment($id)
    {
        $payment = \App\Models\Payment::findOrFail($id);
        
        // Optionally delete proofs from storage...
        foreach($payment->proofs as $proof) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($proof->file_path);
        }
        
        $payment->delete();
        session()->flash('message', 'Payment deleted successfully.');
    }

    public function render()
    {
        $payments = \App\Models\Payment::with(['client', 'order.product', 'allocations.order.product', 'proofs'])
            ->when($this->search, function ($query) {
                $query->where('payment_method', 'like', '%' . $this->search . '%')
                      ->orWhere('amount', 'like', '%' . $this->search . '%')
                      ->orWhere('type', 'like', '%' . $this->search . '%')
                      ->orWhereHas('client', function($q) {
                          $q->where('name', 'like', '%' . $this->search . '%')
                            ->orWhere('email', 'like', '%' . $this->search . '%');
                      })
                      ->orWhereHas('order.product', function($q) {
                          $q->where('name', 'like', '%' . $this->search . '%');
                      });
            })
            ->latest()
            ->paginate(10);

        return view('livewire.payments.payment-index', [
            'payments' => $payments
        ])->layout('layouts.app');
    }
}
