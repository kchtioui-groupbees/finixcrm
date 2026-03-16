<?php

namespace App\Livewire\Clients;

use Livewire\Component;

class ClientIndex extends Component
{
    use \Livewire\WithPagination;

    public $search = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function deleteClient($id)
    {
        \App\Models\Client::findOrFail($id)->delete();
        session()->flash('message', 'Client deleted successfully.');
    }

    public function render()
    {
        $clients = \App\Models\Client::when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%')
                      ->orWhere('phone', 'like', '%' . $this->search . '%');
            })
            ->latest()
            ->paginate(10);

        return view('livewire.clients.client-index', [
            'clients' => $clients
        ])->layout('layouts.app');
    }
}
