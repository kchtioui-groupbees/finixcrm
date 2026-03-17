<?php

namespace App\Livewire\Clients;

use Livewire\Component;

class ClientIndex extends Component
{
    use \Livewire\WithPagination;

    public $search = '';
    public $statusFilter = ''; // active, inactive
    public $balanceFilter = ''; // positive, negative, zero

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function updatingBalanceFilter()
    {
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->reset(['search', 'statusFilter', 'balanceFilter']);
    }

    public function deleteClient($id)
    {
        \App\Models\Client::findOrFail($id)->delete();
        session()->flash('message', 'Client deleted successfully.');
    }

    public function render()
    {
        $clients = \App\Models\Client::query()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%')
                      ->orWhere('phone', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->balanceFilter, function ($query) {
                if ($this->balanceFilter === 'positive') {
                    $query->where('credit_balance', '>', 0);
                } elseif ($this->balanceFilter === 'negative') {
                    $query->where('credit_balance', '<', 0);
                } elseif ($this->balanceFilter === 'zero') {
                    $query->where('credit_balance', 0);
                }
            })
            ->latest()
            ->paginate(10);

        return view('livewire.clients.client-index', [
            'clients' => $clients
        ])->layout('layouts.app');
    }
}
