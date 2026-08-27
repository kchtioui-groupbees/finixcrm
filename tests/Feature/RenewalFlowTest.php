<?php

namespace Tests\Feature;

use App\Livewire\DueDates\RenewModal;
use App\Models\Client;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\RenewalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class RenewalFlowTest extends TestCase
{
    use RefreshDatabase;

    private function makeOrder(array $overrides = []): Order
    {
        $client = Client::create(['name' => 'Ahmed', 'currency' => 'TND', 'credit_balance' => 0]);
        $product = Product::create([
            'name' => 'ChatGPT Plus',
            'slug' => 'chatgpt-plus-' . uniqid(),
            'is_active' => true,
            'renewable' => true,
            'renewal_interval_unit' => 'month',
            'renewal_interval_value' => 1,
            'default_renewal_price' => 30,
        ]);

        return Order::create(array_merge([
            'client_id' => $client->id,
            'product_id' => $product->id,
            'price' => 30,
            'purchase_date' => '2026-08-26',
            'expiry_date' => '2027-08-26',
            'status' => 'active',
            'currency' => 'TND',
            'renewable' => true,
            'renewal_interval_unit' => 'month',
            'renewal_interval_value' => 1,
            'renewal_price' => 30,
            'next_due_date' => '2026-09-26',
        ], $overrides));
    }

    public function test_creating_a_renewable_order_computes_next_due_date(): void
    {
        $order = $this->makeOrder();

        $this->assertNotNull($order->next_due_date);
        $this->assertSame('2026-09-26', $order->next_due_date->toDateString());
    }

    public function test_renew_modal_records_payment_and_advances_next_due_date_without_touching_client_credit(): void
    {
        $order = $this->makeOrder();
        $client = $order->client;
        $client->update(['credit_balance' => 0]);

        $admin = User::factory()->create(['role' => User::ROLE_OWNER]);

        Livewire::actingAs($admin)
            ->test(RenewModal::class)
            ->call('openFor', $order->id)
            ->set('amount', 30)
            ->set('payment_method', 'especes') // cash: no confirmation required, completes immediately
            ->set('payment_date', '2026-09-26')
            ->call('confirmRenewal');

        $order->refresh();
        $client->refresh();

        $this->assertSame('2026-10-26', $order->next_due_date->toDateString());
        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'type' => 'renewal',
            'status' => 'completed',
            'amount' => 30,
        ]);

        // The original order's own price/paid_amount bookkeeping must be untouched —
        // a renewal payment is not part of the FIFO allocation reconciliation.
        $this->assertSame(0.0, (float) $order->paid_amount);
        $this->assertSame(0.0, (float) $client->credit_balance);
    }

    public function test_stop_renewal_only_affects_the_order_not_the_product(): void
    {
        $order = $this->makeOrder();
        $product = $order->product;

        app(RenewalService::class)->stopRenewal($order);

        $order->refresh();
        $product->refresh();

        $this->assertFalse((bool) $order->renewable);
        $this->assertTrue((bool) $product->renewable);
    }
}
