<?php

namespace Tests\Feature;

use App\Livewire\Dashboard\DashboardOverview;
use App\Models\Client;
use App\Models\ClientBalanceTransaction;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use App\Services\DashboardStatsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DashboardOverviewTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => User::ROLE_OWNER]);
    }

    private function makeOrder(array $overrides = []): Order
    {
        $client = Client::create(array_merge(['name' => 'Ahmed', 'currency' => 'TND', 'credit_balance' => 0], $overrides['client'] ?? []));
        unset($overrides['client']);

        $product = Product::create(['name' => 'ChatGPT Plus', 'slug' => 'cgpt-' . uniqid(), 'is_active' => true]);

        return Order::create(array_merge([
            'client_id' => $client->id,
            'product_id' => $product->id,
            'price' => 100,
            'purchase_date' => now()->format('Y-m-d'),
            'expiry_date' => now()->addYear()->format('Y-m-d'),
            'status' => 'active',
            'currency' => 'TND',
            'warranty_start_mode' => 'purchase_date',
        ], $overrides));
    }

    public function test_dashboard_loads_for_admin(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(DashboardOverview::class)
            ->assertOk()
            ->assertSee(__('Period Overview'));
    }

    public function test_only_completed_payments_count_as_revenue_today(): void
    {
        $order = $this->makeOrder();
        $admin = $this->admin();

        Payment::create([
            'client_id' => $order->client_id, 'order_id' => $order->id, 'amount' => 50,
            'payment_method' => 'especes', 'status' => 'completed', 'payment_date' => now()->format('Y-m-d'),
            'type' => 'specific_order', 'currency' => 'TND',
        ]);
        Payment::create([
            'client_id' => $order->client_id, 'order_id' => $order->id, 'amount' => 200,
            'payment_method' => 'virement_bancaire', 'status' => 'pending', 'payment_date' => now()->format('Y-m-d'),
            'type' => 'specific_order', 'currency' => 'TND',
        ]);
        Payment::create([
            'client_id' => $order->client_id, 'order_id' => $order->id, 'amount' => 999,
            'payment_method' => 'especes', 'status' => 'rejected', 'payment_date' => now()->format('Y-m-d'),
            'type' => 'specific_order', 'currency' => 'TND',
        ]);

        $service = app(DashboardStatsService::class);
        [$from, $to] = $service->resolvePeriod('today');
        $stats = $service->getPeriodStats($from, $to);

        $this->assertSame(50.0, (float) $stats['revenue_by_currency']['TND']);
        $this->assertSame(1, $stats['pending_payments']['count']);
    }

    public function test_net_revenue_subtracts_refunds(): void
    {
        $order = $this->makeOrder();

        Payment::create([
            'client_id' => $order->client_id, 'order_id' => $order->id, 'amount' => 100,
            'payment_method' => 'especes', 'status' => 'completed', 'payment_date' => now()->format('Y-m-d'),
            'type' => 'specific_order', 'currency' => 'TND',
        ]);

        ClientBalanceTransaction::create([
            'client_id' => $order->client_id, 'amount' => 30, 'type' => 'refund',
            'description' => 'Partial refund', 'currency' => 'TND',
        ]);

        $service = app(DashboardStatsService::class);
        [$from, $to] = $service->resolvePeriod('today');
        $stats = $service->getPeriodStats($from, $to);

        $this->assertSame(100.0, (float) $stats['revenue_by_currency']['TND']);
        $this->assertSame(30.0, (float) $stats['refunds_by_currency']['TND']);
    }

    public function test_orders_are_split_by_payment_status_within_the_period(): void
    {
        $this->makeOrder(['price' => 100]); // unpaid
        $paidOrder = $this->makeOrder(['price' => 100]);
        Payment::create([
            'client_id' => $paidOrder->client_id, 'order_id' => $paidOrder->id, 'amount' => 100,
            'payment_method' => 'especes', 'status' => 'pending', 'payment_date' => now()->format('Y-m-d'),
            'type' => 'specific_order', 'currency' => 'TND',
        ]);
        $pending = Payment::where('order_id', $paidOrder->id)->first();
        app(\App\Services\PaymentConfirmationService::class)->confirm($pending, $this->admin());

        $service = app(DashboardStatsService::class);
        [$from, $to] = $service->resolvePeriod('today');
        $stats = $service->getPeriodStats($from, $to);

        $this->assertSame(2, $stats['orders_total']);
        $this->assertSame(1, $stats['orders_paid']);
        $this->assertSame(1, $stats['orders_unpaid']);
    }

    public function test_period_switch_changes_the_reported_range(): void
    {
        $admin = $this->admin();

        $test = Livewire::actingAs($admin)->test(DashboardOverview::class);
        $test->assertSet('period', 'today');

        $test->call('setPeriod', 'month');
        $test->assertSet('period', 'month');
    }

    public function test_cashback_distributed_and_used_are_tracked_separately(): void
    {
        $order = $this->makeOrder();

        ClientBalanceTransaction::create([
            'client_id' => $order->client_id, 'amount' => 10, 'type' => 'cashback_reward',
            'description' => 'Reward', 'currency' => 'TND',
        ]);
        ClientBalanceTransaction::create([
            'client_id' => $order->client_id, 'amount' => -4, 'type' => 'usage',
            'description' => 'Applied to order', 'currency' => 'TND',
        ]);

        $service = app(DashboardStatsService::class);
        [$from, $to] = $service->resolvePeriod('today');
        $stats = $service->getPeriodStats($from, $to);

        $this->assertSame(10.0, (float) $stats['cashback_distributed_by_currency']['TND']);
        $this->assertSame(4.0, (float) $stats['cashback_used_by_currency']['TND']);
    }
}
