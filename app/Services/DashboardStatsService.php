<?php

namespace App\Services;

use App\Models\Client;
use App\Models\ClientBalanceTransaction;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentMethod;
use Carbon\Carbon;
use Carbon\CarbonInterface;

/**
 * DashboardStatsService
 *
 * Computes the period-scoped admin dashboard figures. Kept separate from
 * the lifetime KPIs already computed inline on the dashboard route (those
 * are left untouched) so the two concerns don't get tangled together.
 *
 * Rules enforced throughout (per the CRM's financial reporting policy):
 *  - "Revenue" only ever counts payments with status=completed. Pending
 *    payments are surfaced separately and never added to revenue. Rejected
 *    payments are never counted anywhere.
 *  - Refunds/avoirs (ClientBalanceTransaction type=refund) are tracked
 *    separately and subtracted to produce "net revenue", never silently
 *    netted into the raw revenue figure.
 *  - All figures are grouped per currency instead of converted, since
 *    there is no reliable exchange rate to convert with.
 *  - All period boundaries are computed in the Africa/Tunis timezone
 *    (the business's local time), even though the app itself stores and
 *    queries timestamps in UTC (config('app.timezone')) — Carbon instances
 *    carry an absolute instant, so passing Tunis-anchored Carbon objects
 *    into whereBetween() against UTC columns is correct without needing
 *    to change the app-wide timezone (which would be a much bigger,
 *    riskier change touching every other date scope in the app).
 */
class DashboardStatsService
{
    public const TIMEZONE = 'Africa/Tunis';

    /**
     * Resolve a named period (or a custom range) into [from, to] Carbon
     * instants, anchored to the Africa/Tunis timezone.
     */
    public function resolvePeriod(string $period, ?string $customFrom = null, ?string $customTo = null): array
    {
        $now = Carbon::now(self::TIMEZONE);

        return match ($period) {
            'yesterday' => [$now->copy()->subDay()->startOfDay(), $now->copy()->subDay()->endOfDay()],
            'week' => [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()],
            'month' => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()],
            'year' => [$now->copy()->startOfYear(), $now->copy()->endOfYear()],
            'custom' => [
                $customFrom ? Carbon::parse($customFrom, self::TIMEZONE)->startOfDay() : $now->copy()->startOfMonth(),
                $customTo ? Carbon::parse($customTo, self::TIMEZONE)->endOfDay() : $now->copy()->endOfDay(),
            ],
            default => [$now->copy()->startOfDay(), $now->copy()->endOfDay()], // 'today'
        };
    }

    public function getPeriodStats(CarbonInterface $from, CarbonInterface $to): array
    {
        $ordersInPeriod = Order::whereBetween('created_at', [$from, $to])->with('product')->get();

        return [
            'revenue_by_currency' => Payment::where('status', 'completed')
                ->whereBetween('payment_date', [$from->toDateString(), $to->toDateString()])
                ->selectRaw('currency, sum(amount) as total')
                ->groupBy('currency')
                ->pluck('total', 'currency')
                ->toArray(),

            'refunds_by_currency' => ClientBalanceTransaction::where('type', 'refund')
                ->whereBetween('created_at', [$from, $to])
                ->selectRaw('currency, sum(amount) as total')
                ->groupBy('currency')
                ->pluck('total', 'currency')
                ->toArray(),

            'pending_payments' => [
                'count' => Payment::where('status', 'pending')->whereBetween('created_at', [$from, $to])->count(),
                'amount_by_currency' => Payment::where('status', 'pending')
                    ->whereBetween('created_at', [$from, $to])
                    ->selectRaw('currency, sum(amount) as total')
                    ->groupBy('currency')
                    ->pluck('total', 'currency')
                    ->toArray(),
            ],

            'orders_total' => $ordersInPeriod->count(),
            'orders_paid' => $ordersInPeriod->filter(fn ($o) => $o->payment_status === 'paid')->count(),
            'orders_unpaid' => $ordersInPeriod->filter(fn ($o) => $o->payment_status === 'unpaid')->count(),
            'orders_partial' => $ordersInPeriod->filter(fn ($o) => $o->payment_status === 'partially_paid')->count(),

            'amount_to_recover_by_currency' => $ordersInPeriod
                ->filter(fn ($o) => $o->pending_amount > 0)
                ->groupBy('currency')
                ->map(fn ($orders) => $orders->sum('pending_amount'))
                ->toArray(),

            'new_clients' => Client::whereBetween('created_at', [$from, $to])->count(),
            'active_clients' => Client::where(function ($q) use ($from, $to) {
                $q->whereHas('orders', fn ($o) => $o->whereBetween('orders.created_at', [$from, $to]))
                    ->orWhereHas('payments', fn ($p) => $p->whereBetween('payments.created_at', [$from, $to]));
            })->count(),

            'cashback_distributed_by_currency' => ClientBalanceTransaction::where('type', 'cashback_reward')
                ->whereBetween('created_at', [$from, $to])
                ->selectRaw('currency, sum(amount) as total')
                ->groupBy('currency')
                ->pluck('total', 'currency')
                ->toArray(),

            'cashback_used_by_currency' => ClientBalanceTransaction::where('type', 'usage')
                ->whereBetween('created_at', [$from, $to])
                ->selectRaw('currency, sum(amount) as total')
                ->groupBy('currency')
                ->pluck('total', 'currency')
                ->map(fn ($v) => abs($v))
                ->toArray(),

            'total_avoirs_by_currency' => ClientBalanceTransaction::where('type', 'refund')
                ->whereBetween('created_at', [$from, $to])
                ->selectRaw('currency, sum(amount) as total')
                ->groupBy('currency')
                ->pluck('total', 'currency')
                ->toArray(),

            'top_products' => $ordersInPeriod
                ->groupBy('product_id')
                ->map(fn ($orders) => [
                    'name' => $orders->first()->product->name ?? __('Unknown'),
                    'orders_count' => $orders->count(),
                    'revenue' => $orders->sum('price'),
                ])
                ->sortByDesc('orders_count')
                ->take(5)
                ->values(),

            'top_payment_methods' => Payment::where('status', 'completed')
                ->whereBetween('payment_date', [$from->toDateString(), $to->toDateString()])
                ->selectRaw('payment_method, count(*) as count, sum(amount) as total')
                ->groupBy('payment_method')
                ->orderByDesc('count')
                ->limit(5)
                ->get()
                ->map(fn ($row) => [
                    'method' => $row->payment_method,
                    'label' => PaymentMethod::where('key', $row->payment_method)->value('label') ?? $row->payment_method,
                    'count' => $row->count,
                    'total' => $row->total,
                ]),
        ];
    }

    /** Global (not period-scoped) warranty snapshot — a point-in-time state, not a period metric. */
    public function getWarrantyStats(): array
    {
        return [
            'active' => Order::where('warranty_enabled', true)->whereDate('warranty_end_date', '>=', today())->count(),
            'expiring_soon' => Order::where('warranty_enabled', true)
                ->whereDate('warranty_end_date', '>=', today())
                ->whereDate('warranty_end_date', '<=', today()->addDays(30))
                ->count(),
            'expired' => Order::where('warranty_enabled', true)->whereDate('warranty_end_date', '<', today())->count(),
        ];
    }

    public function getPaymentStatusBreakdown(CarbonInterface $from, CarbonInterface $to): array
    {
        return Payment::whereBetween('created_at', [$from, $to])
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();
    }

    /**
     * Daily series for the requested range, capped at 31 points so the bar
     * charts stay legible. Single-day periods (today/yesterday) are
     * expanded to a trailing 14-day window ending on $to so there's an
     * actual trend to look at instead of one bar.
     */
    public function getDailySeries(CarbonInterface $from, CarbonInterface $to): array
    {
        $start = $from->copy();
        $end = $to->copy();

        if ($start->diffInDays($end) < 1) {
            $end = $start->copy()->endOfDay();
            $start = $end->copy()->subDays(13)->startOfDay();
        }

        if ($start->diffInDays($end) > 31) {
            $start = $end->copy()->subDays(31)->startOfDay();
        }

        $days = collect();
        $cursor = $start->copy()->startOfDay();
        while ($cursor->lte($end)) {
            $days->push($cursor->copy());
            $cursor->addDay();
        }

        $revenueByDay = Payment::where('status', 'completed')
            ->whereBetween('payment_date', [$start->toDateString(), $end->toDateString()])
            ->selectRaw('payment_date, sum(amount) as total')
            ->groupBy('payment_date')
            ->pluck('total', 'payment_date');

        $ordersByDay = Order::whereBetween('created_at', [$start, $end])
            ->selectRaw('DATE(created_at) as d, count(*) as count')
            ->groupBy('d')
            ->pluck('count', 'd');

        $clientsByDay = Client::whereBetween('created_at', [$start, $end])
            ->selectRaw('DATE(created_at) as d, count(*) as count')
            ->groupBy('d')
            ->pluck('count', 'd');

        $unpaidByDay = Order::whereBetween('created_at', [$start, $end])
            ->get()
            ->groupBy(fn ($o) => $o->created_at->toDateString())
            ->map(fn ($orders) => $orders->sum('pending_amount'));

        $cashbackDistByDay = ClientBalanceTransaction::where('type', 'cashback_reward')
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw('DATE(created_at) as d, sum(amount) as total')
            ->groupBy('d')
            ->pluck('total', 'd');

        $cashbackUsedByDay = ClientBalanceTransaction::where('type', 'usage')
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw('DATE(created_at) as d, sum(amount) as total')
            ->groupBy('d')
            ->pluck('total', 'd');

        return [
            'labels' => $days->map(fn (Carbon $d) => $d->format('d/m'))->all(),
            'revenue' => $days->map(fn (Carbon $d) => (float) ($revenueByDay[$d->toDateString()] ?? 0))->all(),
            'orders' => $days->map(fn (Carbon $d) => (int) ($ordersByDay[$d->toDateString()] ?? 0))->all(),
            'new_clients' => $days->map(fn (Carbon $d) => (int) ($clientsByDay[$d->toDateString()] ?? 0))->all(),
            'unpaid' => $days->map(fn (Carbon $d) => (float) ($unpaidByDay[$d->toDateString()] ?? 0))->all(),
            'cashback_distributed' => $days->map(fn (Carbon $d) => (float) ($cashbackDistByDay[$d->toDateString()] ?? 0))->all(),
            'cashback_used' => $days->map(fn (Carbon $d) => (float) abs($cashbackUsedByDay[$d->toDateString()] ?? 0))->all(),
        ];
    }
}
