<?php

namespace App\Livewire\Components;

use App\Models\Client;
use Livewire\Component;

class ClientSearch extends Component
{
    public $search = '';
    public $selectedClientId = null;
    public $selectedClientName = '';
    public $results;
    public $showDropdown = false;

    public function __construct() {
        $this->results = collect([]);
    }

    public function mount($selectedClientId = null)
    {
        if ($selectedClientId) {
            $this->selectClient($selectedClientId);
        }
    }

    public function updatedSearch()
    {
        if (strlen($this->search) < 2) {
            $this->results = [];
            $this->showDropdown = false;
            return;
        }

        $this->results = Client::where('name', 'like', '%' . $this->search . '%')
            ->orWhere('email', 'like', '%' . $this->search . '%')
            ->orWhere('phone', 'like', '%' . $this->search . '%')
            ->limit(10)
            ->get();

        $this->showDropdown = true;
    }

    public function selectClient($id)
    {
        $client = Client::find($id);
        if ($client) {
            $this->selectedClientId = $client->id;
            $this->selectedClientName = $client->name;
            $this->search = '';
            $this->results = [];
            $this->showDropdown = false;
            $this->dispatch('client-selected', $client->id);
        }
    }

    public function clearSelection()
    {
        $this->selectedClientId = null;
        $this->selectedClientName = '';
        $this->search = '';
        $this->dispatch('client-selected', null);
    }

    public function render()
    {
        return view('livewire.components.client-search');
    }
}
