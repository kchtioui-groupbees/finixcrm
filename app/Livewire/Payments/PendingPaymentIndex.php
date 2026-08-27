<?php

namespace App\Livewire\Payments;

use App\Models\Client;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Services\PaymentConfirmationService;
use Livewire\Component;
use Livewire\WithPagination;

class PendingPaymentIndex extends Component
{
    use WithPagination;

    public $search = '';
    public $client_id = '';
    public $product_id = '';
    public $payment_method = '';
    public $date_from = '';
    public $date_to = '';
    public $min_amount = '';
    public $max_amount = '';
    public $olderThanDays = ''; // "ancienneté" quick filter

    public function updated($property)
    {
        if (in_array($property, ['search', 'client_id', 'product_id', 'payment_method', 'date_from', 'date_to', 'min_amount', 'max_amount', 'olderThanDays'])) {
            $this->resetPage();
        }
    }

    public function confirm(int $paymentId)
    {
        $payment = Payment::findOrFail($paymentId);

        try {
            app(PaymentConfirmationService::class)->confirm($payment, auth()->user());
            session()->flash('message', __('Payment confirmed.'));
        } catch (\RuntimeException $e) {
            session()->flash('error', __('This payment could not be confirmed — it may have already been processed.'));
        }
    }

    public function reject(int $paymentId)
    {
        $payment = Payment::findOrFail($paymentId);

        try {
            app(PaymentConfirmationService::class)->reject($payment, auth()->user());
            session()->flash('message', __('Payment rejected.'));
        } catch (\RuntimeException $e) {
            session()->flash('error', __('This payment could not be rejected — it may have already been processed.'));
        }
    }

    protected function baseQuery()
    {
        $query = Payment::where('status', 'pending')
            ->with(['client', 'order.product']);

        if ($this->client_id) {
            $query->where('client_id', $this->client_id);
        }

        if ($this->product_id) {
            $query->whereHas('order', fn ($q) => $q->where('product_id', $this->product_id));
        }

        if ($this->payment_method) {
            $query->where('payment_method', $this->payment_method);
        }

        if ($this->date_from) {
            $query->whereDate('payment_date', '>=', $this->date_from);
        }

        if ($this->date_to) {
            $query->whereDate('payment_date', '<=', $this->date_to);
        }

        if ($this->min_amount !== '') {
            $query->where('amount', '>=', $this->min_amount);
        }

        if ($this->max_amount !== '') {
            $query->where('amount', '<=', $this->max_amount);
        }

        if ($this->olderThanDays !== '') {
            $query->where('created_at', '<=', now()->subDays((int) $this->olderThanDays));
        }

        if ($this->search) {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                    ->orWhere('internal_notes', 'like', "%{$search}%")
                    ->orWhereHas('client', fn ($c) => $c->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('order.product', fn ($p) => $p->where('name', 'like', "%{$search}%"));
            });
        }

        return $query;
    }

    public function render()
    {
        $payments = $this->baseQuery()->latest()->paginate(15);

        return view('livewire.payments.pending-payment-index', [
            'payments' => $payments,
            'clients' => Client::orderBy('name')->get(['id', 'name']),
            'products' => Product::orderBy('name')->get(['id', 'name']),
            'paymentMethods' => PaymentMethod::active()->get(),
        ])->layout('layouts.app');
    }
}
