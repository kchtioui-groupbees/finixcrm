<?php

namespace App\Livewire\DueDates;

use App\Models\Order;
use App\Models\Product;
use Carbon\Carbon;
use Livewire\Component;

class DueDateCalendar extends Component
{
    public $month; // Y-m
    public $product_id = '';
    public $selectedDate = null;

    protected $listeners = ['renewal-recorded' => '$refresh'];

    public function mount()
    {
        $this->month = now()->format('Y-m');
    }

    public function previousMonth()
    {
        $this->month = Carbon::parse($this->month . '-01')->subMonth()->format('Y-m');
        $this->selectedDate = null;
    }

    public function nextMonth()
    {
        $this->month = Carbon::parse($this->month . '-01')->addMonth()->format('Y-m');
        $this->selectedDate = null;
    }

    public function selectDate($date)
    {
        $this->selectedDate = $this->selectedDate === $date ? null : $date;
    }

    protected function monthlyAggregates()
    {
        $start = Carbon::parse($this->month . '-01')->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $query = Order::renewable()
            ->whereBetween('next_due_date', [$start->toDateString(), $end->toDateString()]);

        if ($this->product_id) {
            $query->where('product_id', $this->product_id);
        }

        return $query
            ->selectRaw('next_due_date, COUNT(*) as due_count, SUM(renewal_price) as due_total')
            ->groupBy('next_due_date')
            ->get()
            ->keyBy(fn ($row) => Carbon::parse($row->next_due_date)->format('Y-m-d'));
    }

    public function render()
    {
        $start = Carbon::parse($this->month . '-01')->startOfMonth();
        $end = $start->copy()->endOfMonth();
        $aggregates = $this->monthlyAggregates();

        $leadingBlanks = $start->dayOfWeekIso - 1; // Monday-first calendar

        $days = [];
        for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
            $key = $d->format('Y-m-d');
            $days[] = [
                'date' => $key,
                'day' => $d->day,
                'is_today' => $d->isToday(),
                'count' => $aggregates[$key]->due_count ?? 0,
                'total' => $aggregates[$key]->due_total ?? 0,
            ];
        }

        $selectedOrders = collect();
        if ($this->selectedDate) {
            $selectedOrders = Order::renewable()
                ->whereDate('next_due_date', $this->selectedDate)
                ->when($this->product_id, fn ($q) => $q->where('product_id', $this->product_id))
                ->with(['client', 'product'])
                ->orderBy('client_id')
                ->get();
        }

        return view('livewire.due-dates.due-date-calendar', [
            'days' => $days,
            'leadingBlanks' => $leadingBlanks,
            'monthLabel' => $start->translatedFormat('F Y'),
            'products' => Product::orderBy('name')->get(['id', 'name']),
            'selectedOrders' => $selectedOrders,
        ])->layout('layouts.app');
    }
}
