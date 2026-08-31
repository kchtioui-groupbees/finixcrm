<?php

namespace App\Livewire\Clients;

use App\Models\Client;
use App\Models\ClientBalanceTransaction;
use App\Models\Order;
use App\Models\PaymentAllocation;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class ClientTransactions extends Component
{
    public Client $client;
    public $amountToApply = 0;
    public $selectedOrderId = '';
    public $showApplyModal = false;

    // Manual adjustment
    public $manualAmount = '';
    public $manualType = 'manual_adjustment';
    public $manualDescription = '';
    public $showManualModal = false;

    public function mount(Client $client)
    {
        $this->client = $client;
    }

    public function applyCredit()
    {
        $this->validate([
            'selectedOrderId' => 'required|exists:orders,id',
            'amountToApply' => 'required|numeric|min:0.01|max:' . $this->client->credit_balance,
        ]);

        DB::transaction(function() {
            $order = Order::find($this->selectedOrderId);
            $due = $order->pending_amount;

            if ($this->amountToApply > $due) {
                $this->amountToApply = $due;
            }

            // 1. Create Balance Transaction (Debit)
            $transaction = ClientBalanceTransaction::create([
                'client_id' => $this->client->id,
                'amount' => -$this->amountToApply,
                'type' => 'usage',
                'description' => "Applied credit to order #{$order->id}",
                'reference_type' => 'order',
                'reference_id' => $order->id,
                'created_by' => auth()->id(),
            ]);

            // 2. Create Allocation
            PaymentAllocation::create([
                'balance_transaction_id' => $transaction->id,
                'order_id' => $order->id,
                'amount' => $this->amountToApply,
            ]);

            // 3. Refresh Client Balance
            $this->client->refreshBalance();
        });

        $this->showApplyModal = false;
        $this->reset(['selectedOrderId', 'amountToApply']);
        session()->flash('message', 'Credit applied successfully.');
    }

    public function manualAdjustment()
    {
        $this->validate([
            'manualAmount' => 'required|numeric',
            'manualDescription' => 'required|string|max:255',
            'manualType' => 'required|in:manual_adjustment,refund',
        ]);

        ClientBalanceTransaction::create([
            'client_id' => $this->client->id,
            'amount' => $this->manualAmount,
            'type' => $this->manualType,
            'description' => $this->manualDescription,
            'created_by' => auth()->id(),
        ]);

        $this->client->refreshBalance();

        $this->showManualModal = false;
        $this->reset(['manualAmount', 'manualDescription', 'manualType']);
        session()->flash('message', 'Balance adjustment saved.');
    }

    public function render()
    {
        $transactions = $this->client->balanceTransactions()->latest()->paginate(10);
        $unpaidOrders = $this->client->orders()->get()->filter(function($o) {
            return $o->payment_status !== 'paid';
        });

        return view('livewire.clients.client-transactions', [
            'transactions' => $transactions,
            'unpaidOrders' => $unpaidOrders
        ])->layout('layouts.app');
    }
}
