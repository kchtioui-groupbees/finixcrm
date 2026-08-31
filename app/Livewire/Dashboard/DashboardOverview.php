<?php

namespace App\Livewire\Dashboard;

use App\Models\Client;
use App\Models\Order;
use App\Models\Payment;
use App\Services\DashboardStatsService;
use Livewire\Component;

class DashboardOverview extends Component
{
    public string $period = 'today';
    public string $customFrom = '';
    public string $customTo = '';

    public function mount()
    {
        $this->customFrom = now(DashboardStatsService::TIMEZONE)->startOfMonth()->format('Y-m-d');
        $this->customTo = now(DashboardStatsService::TIMEZONE)->format('Y-m-d');
    }

    public function setPeriod(string $period)
    {
        $this->period = $period;
    }

    public function render()
    {
        $service = app(DashboardStatsService::class);

        [$from, $to] = $service->resolvePeriod($this->period, $this->customFrom, $this->customTo);

        return view('livewire.dashboard.dashboard-overview', array_merge(
            $this->legacyLifetimeStats(),
            [
                'periodFrom' => $from,
                'periodTo' => $to,
                'periodStats' => $service->getPeriodStats($from, $to),
                'warrantyStats' => $service->getWarrantyStats(),
                'paymentStatusBreakdown' => $service->getPaymentStatusBreakdown($from, $to),
                'dailySeries' => $service->getDailySeries($from, $to),
            ]
        ))->layout('layouts.app');
    }

    /**
     * The dashboard's original always-on lifetime KPIs (total revenue,
     * due-dates board, upcoming renewals, actionable reminders...) —
     * unchanged from before this component existed, just moved out of the
     * old route closure so they still render alongside the new
     * period-filtered panel below them.
     */
    private function legacyLifetimeStats(): array
    {
        $revenuePerCurrency = Payment::where('status', 'completed')
            ->selectRaw('currency, sum(amount) as total')
            ->groupBy('currency')
            ->pluck('total', 'currency')
            ->toArray();

        $pendingRevenuePerCurrency = Order::all()
            ->groupBy('currency')
            ->map(fn ($orders) => $orders->sum('pending_amount'))
            ->filter(fn ($amount) => $amount > 0)
            ->toArray();

        $dueDateKpi = function ($query) {
            return [
                'count' => (clone $query)->count(),
                'amount_per_currency' => (clone $query)
                    ->selectRaw('currency, sum(renewal_price) as total')
                    ->groupBy('currency')
                    ->pluck('total', 'currency')
                    ->toArray(),
            ];
        };

        $dueDates = [
            'today' => $dueDateKpi(Order::dueToday()),
            'next7' => $dueDateKpi(Order::dueWithin(7)),
            'next30' => $dueDateKpi(Order::dueWithin(30)),
            'overdue' => $dueDateKpi(Order::overdueRenewals()),
        ];

        $upcomingDueDates = Order::renewable()
            ->with(['client', 'product'])
            ->orderBy('next_due_date')
            ->limit(10)
            ->get();

        $pendingPaymentsQuery = Payment::where('status', 'pending');
        $pendingPaymentsKpi = [
            'count' => (clone $pendingPaymentsQuery)->count(),
            'amount_per_currency' => (clone $pendingPaymentsQuery)
                ->selectRaw('currency, sum(amount) as total')
                ->groupBy('currency')
                ->pluck('total', 'currency')
                ->toArray(),
            'old_count' => (clone $pendingPaymentsQuery)->where('created_at', '<=', now()->subDays(3))->count(),
        ];

        return [
            'clientsCount' => Client::count(),
            'ordersCount' => Order::count(),
            'revenuePerCurrency' => $revenuePerCurrency,
            'pendingRevenuePerCurrency' => $pendingRevenuePerCurrency,
            'clientCreditPerCurrency' => Client::where('credit_balance', '>', 0)
                ->selectRaw('currency, sum(credit_balance) as total')
                ->groupBy('currency')
                ->pluck('total', 'currency')
                ->toArray(),
            'activeProductsCount' => Order::active()->count(),
            'expiringSoonCount' => Order::expiringSoon()->count(),
            'expiredProductsCount' => Order::expired()->count(),
            'reminders' => Order::expiringSoon()
                ->with(['client'])
                ->limit(5)
                ->get()
                ->map(fn ($o) => [
                    'type' => 'expiring',
                    'order_id' => $o->id,
                    'client_name' => $o->client->name,
                    'product_name' => $o->product->name,
                    'days' => now()->diffInDays($o->expiry_date, false),
                ]),
            'dueDates' => $dueDates,
            'upcomingDueDates' => $upcomingDueDates,
            'pendingPaymentsKpi' => $pendingPaymentsKpi,
        ];
    }
}
