<?php

namespace App\Livewire\Clients;

use App\Models\Order;
use App\Models\Payment;
use App\Services\PaymentConfirmationService;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Component;
use Livewire\WithPagination;

class ClientUnpaidIndex extends Component
{
    use WithPagination;

    public $activeTab = 'unpaid';
    public $search = '';
    public $statusFilter = '';
    public $perPage = 15;

    public function mount()
    {
        $this->activeTab = request()->query('tab', 'unpaid');
    }

    public function setTab($tab)
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }

    public function updated($property)
    {
        if (in_array($property, ['search', 'statusFilter'])) {
            $this->resetPage();
        }
    }

    public function confirmPayment(int $paymentId)
    {
        $payment = Payment::findOrFail($paymentId);

        try {
            app(PaymentConfirmationService::class)->confirm($payment, auth()->user());
            session()->flash('message', __('Payment confirmed.'));
        } catch (\RuntimeException $e) {
            session()->flash('error', __('This payment could not be confirmed — it may have already been processed.'));
        }
    }

    public function rejectPayment(int $paymentId)
    {
        $payment = Payment::findOrFail($paymentId);

        try {
            app(PaymentConfirmationService::class)->reject($payment, auth()->user());
            session()->flash('message', __('Payment rejected.'));
        } catch (\RuntimeException $e) {
            session()->flash('error', __('This payment could not be rejected — it may have already been processed.'));
        }
    }

    /**
     * The remaining-amount formula is already implemented in
     * Order::pending_amount = price - paid_amount, where paid_amount only
     * sums allocations backed by a *completed* payment or a Finix balance
     * usage — pending payments never count. This mirrors the exact
     * "Montant restant = total - paiements validés - solde utilisé"
     * requirement without duplicating the logic.
     *
     * There is no per-order discount field in the schema, so there is
     * nothing to subtract for "réductions appliquées" today.
     */
    private function annotatedOrder(Order $order): array
    {
        $pendingPayment = $order->payments->firstWhere('status', 'pending');
        $rejectedPayment = $order->payments->where('status', 'rejected')->sortByDesc('created_at')->first();
        $lastPayment = $order->payments->sortByDesc('payment_date')->first();
        $lastNotedPayment = $order->payments->filter(fn ($p) => filled($p->internal_notes))->sortByDesc('created_at')->first();

        $isLate = $order->pending_amount > 0 && $order->next_due_date && $order->next_due_date->isPast();

        if ($pendingPayment) {
            $status = 'en_attente_verification';
        } elseif ($order->pending_amount <= 0) {
            $status = 'paye';
        } elseif ($isLate) {
            $status = 'en_retard';
        } elseif ($order->paid_amount <= 0) {
            $status = 'impaye';
        } else {
            $status = 'partiel';
        }

        return [
            'order' => $order,
            'status' => $status,
            'pending_payment' => $pendingPayment,
            'rejected_payment' => $rejectedPayment,
            'last_payment' => $lastPayment,
            'last_note' => $lastNotedPayment?->internal_notes,
            'proof' => ($pendingPayment ?? $rejectedPayment)?->proofs->first(),
        ];
    }

    private function unpaidOrdersQuery()
    {
        $query = Order::with(['client', 'product', 'payments.proofs'])
            ->whereHas('client');

        if ($this->search) {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('client', fn ($c) => $c->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%"))
                    ->orWhereHas('product', fn ($p) => $p->where('name', 'like', "%{$search}%"));
            });
        }

        return $query;
    }

    /**
     * Order::pending_amount / payment_status are computed accessors (not
     * DB columns), so filtering by them has to happen in PHP after
     * fetching. We paginate the already-filtered collection manually to
     * keep the "no incorrect figures from premature caching" rule intact.
     */
    private function paginateAnnotated(\Illuminate\Support\Collection $annotated): LengthAwarePaginator
    {
        $page = $this->getPage();
        $slice = $annotated->slice(($page - 1) * $this->perPage, $this->perPage)->values();

        return new LengthAwarePaginator($slice, $annotated->count(), $this->perPage, $page, [
            'path' => request()->url(),
            'query' => request()->query(),
        ]);
    }

    public function render()
    {
        $data = ['activeTab' => $this->activeTab];

        if ($this->activeTab === 'partial') {
            $annotated = $this->unpaidOrdersQuery()->get()
                ->map(fn ($o) => $this->annotatedOrder($o))
                ->filter(fn ($row) => $row['status'] === 'partiel' || ($row['order']->paid_amount > 0 && $row['order']->pending_amount > 0));

            $data['rows'] = $this->paginateAnnotated($annotated->values());
        } elseif ($this->activeTab === 'rejected') {
            $data['payments'] = Payment::where('status', 'rejected')
                ->with(['client', 'order.product', 'proofs', 'rejectedBy'])
                ->latest('rejected_at')
                ->paginate($this->perPage);
        } elseif ($this->activeTab === 'unattached') {
            $data['payments'] = Payment::whereNull('client_id')
                ->with(['order.product', 'proofs'])
                ->latest()
                ->paginate($this->perPage);
        } else {
            $annotated = $this->unpaidOrdersQuery()->get()
                ->map(fn ($o) => $this->annotatedOrder($o))
                ->filter(fn ($row) => $row['status'] !== 'paye');

            if ($this->statusFilter) {
                $annotated = $annotated->filter(fn ($row) => $row['status'] === $this->statusFilter);
            }

            $data['rows'] = $this->paginateAnnotated($annotated->values());
        }

        return view('livewire.clients.client-unpaid-index', $data)->layout('layouts.app');
    }
}
