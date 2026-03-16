<?php

namespace App\Livewire\Clients;

use App\Models\Client;
use App\Models\Order;
use App\Models\Payment;
use App\Models\ClientBalanceTransaction;
use App\Models\WarrantyClaim;
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
            $data['transactions'] = $this->client->balanceTransactions()->latest()->paginate(10);
        } elseif ($this->activeTab === 'warranty') {
            $data['activeWarranties'] = $this->client->orders()
                ->where('warranty_enabled', 1)
                ->whereDate('warranty_end_date', '>=', now())
                ->with('product')
                ->get();
            $data['claims'] = $this->client->warrantyClaims()->with('order.product')->latest()->paginate(10);
        }

        return view('livewire.clients.client-show', $data)->layout('layouts.app');
    }
}
