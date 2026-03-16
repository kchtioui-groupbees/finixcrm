<?php

namespace App\Livewire\WarrantyClaims;

use App\Models\WarrantyClaim;
use Livewire\Component;
use Livewire\WithPagination;

class ClaimIndex extends Component
{
    use WithPagination;

    public $search = '';
    public $status = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => ''],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updateStatus($claimId, $newStatus)
    {
        $claim = WarrantyClaim::findOrFail($claimId);
        $claim->update(['status' => $newStatus]);
        session()->flash('message', 'Claim status updated to ' . $newStatus);
    }

    public function deleteClaim($claimId)
    {
        WarrantyClaim::findOrFail($claimId)->delete();
        session()->flash('message', 'Claim deleted successfully.');
    }

    public function render()
    {
        $claims = WarrantyClaim::with(['order.client', 'order.product'])
            ->when($this->status, function($query) {
                $query->where('status', $this->status);
            })
            ->when($this->search, function($query) {
                $query->whereHas('order.client', function($q) {
                    $q->where('name', 'like', '%' . $this->search . '%');
                })->orWhere('subject', 'like', '%' . $this->search . '%');
            })
            ->latest()
            ->paginate(10);

        return view('livewire.warranty-claims.claim-index', [
            'claims' => $claims
        ])->layout('layouts.app');
    }
}
