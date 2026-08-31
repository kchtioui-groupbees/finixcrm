<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WarrantyTrackingTest extends TestCase
{
    use RefreshDatabase;

    private function makeOrder(array $overrides = []): Order
    {
        $client = Client::create(array_merge(['name' => 'Ahmed', 'currency' => 'TND', 'credit_balance' => 0], $overrides['client'] ?? []));
        unset($overrides['client']);

        $product = Product::create(['name' => 'ChatGPT Plus', 'slug' => 'cgpt-' . uniqid(), 'is_active' => true]);

        return Order::create(array_merge([
            'client_id' => $client->id,
            'product_id' => $product->id,
            'price' => 100,
            'purchase_date' => now()->subMonths(2)->format('Y-m-d'),
            'expiry_date' => now()->addYear()->format('Y-m-d'),
            'status' => 'active',
            'currency' => 'TND',
            'warranty_start_mode' => 'purchase_date',
        ], $overrides));
    }

    public function test_warranty_days_remaining_counts_down_to_the_end_date(): void
    {
        $order = $this->makeOrder([
            'warranty_enabled' => true,
            'warranty_end_date' => now()->addDays(10)->format('Y-m-d'),
        ]);

        $this->assertSame(10, $order->warranty_days_remaining);
    }

    public function test_warranty_days_remaining_is_negative_once_past_end_date(): void
    {
        $order = $this->makeOrder([
            'warranty_enabled' => true,
            'warranty_end_date' => now()->subDays(5)->format('Y-m-d'),
        ]);

        $this->assertSame(-5, $order->warranty_days_remaining);
    }

    public function test_warranty_days_remaining_is_null_when_warranty_is_disabled(): void
    {
        $order = $this->makeOrder(['warranty_enabled' => false]);

        $this->assertNull($order->warranty_days_remaining);
    }

    public function test_client_warranty_active_and_expired_counts(): void
    {
        $activeOrder = $this->makeOrder([
            'warranty_enabled' => true,
            'warranty_end_date' => now()->addDays(30)->format('Y-m-d'),
        ]);
        $client = $activeOrder->client;

        $product2 = Product::create(['name' => 'Netflix', 'slug' => 'netflix-' . uniqid(), 'is_active' => true]);
        Order::create([
            'client_id' => $client->id,
            'product_id' => $product2->id,
            'price' => 30,
            'purchase_date' => now()->subYear()->format('Y-m-d'),
            'expiry_date' => now()->addMonth()->format('Y-m-d'),
            'status' => 'active',
            'currency' => 'TND',
            'warranty_start_mode' => 'purchase_date',
            'warranty_enabled' => true,
            'warranty_end_date' => now()->subDays(3)->format('Y-m-d'),
        ]);

        $this->assertSame(1, $client->warranty_active_count);
        $this->assertSame(1, $client->warranty_expired_count);
    }

    public function test_warranties_expiring_within_30_days_are_flagged_for_the_dashboard(): void
    {
        $this->makeOrder([
            'warranty_enabled' => true,
            'warranty_end_date' => now()->addDays(15)->format('Y-m-d'),
        ]);
        $this->makeOrder([
            'client' => ['name' => 'Sara'],
            'warranty_enabled' => true,
            'warranty_end_date' => now()->addDays(90)->format('Y-m-d'),
        ]);

        $stats = app(\App\Services\DashboardStatsService::class)->getWarrantyStats();

        $this->assertSame(2, $stats['active']);
        $this->assertSame(1, $stats['expiring_soon']);
    }
}
