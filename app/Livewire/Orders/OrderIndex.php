<?php

namespace App\Livewire\Orders;

use Livewire\Component;

class OrderIndex extends Component
{
    use \Livewire\WithPagination;

    public $search = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function deleteOrder($id)
    {
        \App\Models\Order::findOrFail($id)->delete();
        session()->flash('message', 'Order deleted successfully.');
    }

    public function render()
    {
        $orders = \App\Models\Order::with(['client', 'product'])
            ->when($this->search, function ($query) {
                $query->whereHas('product', function($q) {
                          $q->where('name', 'like', '%' . $this->search . '%');
                      })
                      ->orWhereHas('client', function($q) {
                          $q->where('name', 'like', '%' . $this->search . '%');
                      });
            })
            ->latest()
            ->paginate(10);

        return view('livewire.orders.order-index', [
            'orders' => $orders
        ])->layout('layouts.app');
    }
}
