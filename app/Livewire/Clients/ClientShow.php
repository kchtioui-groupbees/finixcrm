<?php

namespace App\Livewire\Clients;

use App\Models\Client;
use App\Models\Order;
use App\Models\Payment;
use App\Models\ClientBalanceTransaction;
use App\Models\WarrantyClaim;
use App\Services\FinixBalanceAutoApplyService;
use Livewire\Component;
use Livewire\WithPagination;

class ClientShow extends Component
{
    use WithPagination;

    public Client $client;
    public $activeTab = 'overview';

    // Note state
    public $newNote = '';

    public function mount(Client $client)
    {
        $this->client = $client->load(['orders.product', 'payments', 'balanceTransactions', 'warrantyClaims']);
        $this->activeTab = request()->query('tab', 'overview');
    }

    public function setTab($tab)
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }

    /**
     * The only way an automatic Finix balance application can be undone —
     * a controlled, explicit admin action. Reversal is guarded inside the
     * service itself (only 'usage' transactions with created_by null are
     * reversible).
     */
    public function reverseAutoApplication(int $transactionId)
    {
        $transaction = ClientBalanceTransaction::findOrFail($transactionId);

        try {
            app(FinixBalanceAutoApplyService::class)->reverseApplication($transaction, auth()->user());
            $this->client->refresh();
            session()->flash('message', __('Automatic balance application reversed.'));
        } catch (\RuntimeException $e) {
            session()->flash('error', __('This transaction cannot be reversed.'));
        }
    }

    public function addNote()
    {
        $this->validate([
            'newNote' => 'required|string|min:3'
        ]);

        // For now, we'll append to the client's notes field with a timestamp 
        // since we haven't created a separate Notes table yet.
        // But the user wants a "Notes" tab, so let's check if we should create a table.
        // Actually, let's just append for now to avoid migration overhead unless necessary.
        $timestamp = now()->format('Y-m-d H:i');
        $user = auth()->user()->name;
        $currentNotes = $this->client->notes ? $this->client->notes . "\n\n" : "";
        $this->client->update([
            'notes' => $currentNotes . "--- {$timestamp} ({$user}) ---\n" . $this->newNote
        ]);

        $this->newNote = '';
        $this->client->refresh();
        session()->flash('message', 'Note added successfully.');
    }

    public function render()
    {
        $data = [];

        if ($this->activeTab === 'orders') {
            $data['orders'] = $this->client->orders()->with('product')->latest()->paginate(10);
        } elseif ($this->activeTab === 'transactions') {
            // Combine payments and credit usages if needed, or just list payments
            $data['payments'] = $this->client->payments()->latest()->paginate(10);
        } elseif ($this->activeTab === 'balance') {
            $data['transactions'] = $this->client->balanceTransactions()->with('createdBy')->latest()->paginate(10);
        } elseif ($this->activeTab === 'warranty') {
            $data['activeWarranties'] = $this->client->orders()
                ->where('warranty_enabled', 1)
                ->whereDate('warranty_end_date', '>=', now())
                ->with('product')
                ->get();
            $data['claims'] = $this->client->warrantyClaims()->with('order.product')->latest()->paginate(10);
        } elseif ($this->activeTab === 'cashback') {
            $data['cashbackOrders'] = $this->client->orders()
                ->with('product')
                ->where('cashback_enabled_snapshot', true)
                ->where('cashback_amount', '>', 0)
                ->latest()
                ->paginate(10);
        } elseif ($this->activeTab === 'history') {
            $data['history'] = $this->buildHistoryTimeline();
        }

        return view('livewire.clients.client-show', $data)->layout('layouts.app');
    }

    /**
     * There is no dedicated audit-log table, so "Historique des
     * modifications" is assembled from the timestamps already recorded on
     * existing rows (orders, payments, balance movements, warranty claims)
     * rather than introducing a new table for it.
     */
    private function buildHistoryTimeline(): \Illuminate\Support\Collection
    {
        $events = collect();

        foreach ($this->client->orders as $order) {
            $events->push([
                'date' => $order->created_at,
                'type' => 'order',
                'label' => __('Order created'),
                'description' => $order->product->name ?? __('Unknown product'),
            ]);
        }

        foreach ($this->client->payments as $payment) {
            $events->push([
                'date' => $payment->created_at,
                'type' => 'payment',
                'label' => __('Payment recorded'),
                'description' => $this->client->formatAmount($payment->amount) . ' — ' . __($payment->status),
            ]);
        }

        foreach ($this->client->balanceTransactions as $tx) {
            $events->push([
                'date' => $tx->created_at,
                'type' => 'balance',
                'label' => __('Balance movement'),
                'description' => ($tx->amount > 0 ? '+' : '') . $this->client->formatAmount($tx->amount) . ' — ' . str_replace('_', ' ', $tx->type),
            ]);
        }

        foreach ($this->client->warrantyClaims as $claim) {
            $events->push([
                'date' => $claim->created_at,
                'type' => 'warranty',
                'label' => __('Warranty claim opened'),
                'description' => __($claim->status),
            ]);
        }

        return $events->sortByDesc('date')->values()->take(50);
    }
}
