<?php

namespace App\Livewire\DueDates;

use App\Models\Client;
use App\Models\Order;
use App\Models\Product;
use Livewire\Component;
use Livewire\WithPagination;

class DueDateIndex extends Component
{
    use WithPagination;

    public $search = '';
    public $client_id = '';
    public $product_id = '';
    public $quickFilter = ''; // '', today, week, next7, next30, overdue
    public $date_from = '';
    public $date_to = '';
    public $unconfigured = false; // "Abonnements sans échéance configurée"

    protected $queryString = ['quickFilter', 'client_id', 'product_id', 'search'];
    protected $listeners = ['renewal-recorded' => '$refresh'];

    public function updated($property)
    {
        if (in_array($property, ['search', 'client_id', 'product_id', 'quickFilter', 'date_from', 'date_to', 'unconfigured'])) {
            $this->resetPage();
        }
    }

    public function setQuickFilter(string $filter)
    {
        $this->quickFilter = $this->quickFilter === $filter ? '' : $filter;
        $this->date_from = '';
        $this->date_to = '';
        $this->unconfigured = false;
    }

    public function toggleUnconfigured()
    {
        $this->unconfigured = !$this->unconfigured;
        if ($this->unconfigured) {
            $this->quickFilter = '';
            $this->date_from = '';
            $this->date_to = '';
        }
    }

    protected function baseQuery()
    {
        if ($this->unconfigured) {
            $query = Order::query()
                ->whereHas('product', fn ($q) => $q->where('renewable', true))
                ->where(function ($q) {
                    $q->whereNull('renewal_interval_unit')
                        ->orWhereNull('next_due_date');
                });
        } else {
            $query = Order::renewable();

            if ($this->quickFilter === 'today') {
                $query->dueToday();
            } elseif ($this->quickFilter === 'week') {
                $query->dueWithin(7 - today()->dayOfWeekIso);
            } elseif ($this->quickFilter === 'next7') {
                $query->dueWithin(7);
            } elseif ($this->quickFilter === 'next30') {
                $query->dueWithin(30);
            } elseif ($this->quickFilter === 'overdue') {
                $query->overdueRenewals();
            }

            if ($this->date_from) {
                $query->whereDate('next_due_date', '>=', $this->date_from);
            }
            if ($this->date_to) {
                $query->whereDate('next_due_date', '<=', $this->date_to);
            }
        }

        if ($this->client_id) {
            $query->where('client_id', $this->client_id);
        }

        if ($this->product_id) {
            $query->where('product_id', $this->product_id);
        }

        if ($this->search) {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('client', fn ($c) => $c->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('product', fn ($p) => $p->where('name', 'like', "%{$search}%"));
            });
        }

        return $query;
    }

    public function render()
    {
        $orders = $this->baseQuery()
            ->with(['client', 'product'])
            ->orderBy('next_due_date', 'asc')
            ->paginate(15);

        return view('livewire.due-dates.due-date-index', [
            'orders' => $orders,
            'clients' => Client::orderBy('name')->get(['id', 'name']),
            'products' => Product::orderBy('name')->get(['id', 'name']),
        ])->layout('layouts.app');
    }
}
