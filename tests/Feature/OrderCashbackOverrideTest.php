<?php

namespace Tests\Feature;

use App\Livewire\Orders\OrderForm;
use App\Models\Client;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class OrderCashbackOverrideTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => User::ROLE_OWNER]);
    }

    private function makeClientAndProduct(): array
    {
        $client = Client::create(['name' => 'Ahmed', 'currency' => 'TND', 'credit_balance' => 0]);
        $product = Product::create([
            'name' => 'ChatGPT Plus',
            'slug' => 'chatgpt-plus-' . uniqid(),
            'is_active' => true,
            'cashback_enabled' => true,
            'cashback_type' => 'fixed',
            'cashback_value' => 5,
        ]);

        return [$client, $product];
    }

    public function test_cashback_defaults_from_the_product_when_creating_an_order(): void
    {
        [$client, $product] = $this->makeClientAndProduct();
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(OrderForm::class)
            ->set('client_id', $client->id)
            ->set('product_id', $product->id)
            ->assertSet('cashback_enabled', true)
            ->assertSet('cashback_type', 'fixed')
            ->assertSet('cashback_value', '5.000');
    }

    public function test_admin_can_override_the_cashback_amount_at_order_creation(): void
    {
        [$client, $product] = $this->makeClientAndProduct();
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(OrderForm::class)
            ->set('client_id', $client->id)
            ->set('product_id', $product->id)
            ->set('price', 100)
            ->set('purchase_date', '2026-08-01')
            ->set('expiry_date', '2027-08-01')
            ->set('cashback_value', 15)
            ->set('cashback_note', 'Promo été exceptionnelle')
            ->call('save');

        $order = Order::where('client_id', $client->id)->first();

        $this->assertSame(15.0, (float) $order->cashback_amount);
        $this->assertSame('Promo été exceptionnelle', $order->cashback_note);
    }

    public function test_admin_can_zero_out_cashback_for_a_specific_order(): void
    {
        [$client, $product] = $this->makeClientAndProduct();
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(OrderForm::class)
            ->set('client_id', $client->id)
            ->set('product_id', $product->id)
            ->set('price', 100)
            ->set('purchase_date', '2026-08-01')
            ->set('expiry_date', '2027-08-01')
            ->set('cashback_enabled', false)
            ->call('save');

        $order = Order::where('client_id', $client->id)->first();

        $this->assertFalse((bool) $order->cashback_enabled_snapshot);
        $this->assertSame(0.0, (float) $order->cashback_amount);
    }

    public function test_admin_can_switch_cashback_to_percentage_type(): void
    {
        [$client, $product] = $this->makeClientAndProduct();
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(OrderForm::class)
            ->set('client_id', $client->id)
            ->set('product_id', $product->id)
            ->set('price', 200)
            ->set('purchase_date', '2026-08-01')
            ->set('expiry_date', '2027-08-01')
            ->set('cashback_type', 'percentage')
            ->set('cashback_value', 10)
            ->call('save');

        $order = Order::where('client_id', $client->id)->first();

        $this->assertSame('percentage', $order->cashback_type_snapshot);
        $this->assertSame(20.0, (float) $order->cashback_amount);
    }

    public function test_changing_the_product_later_never_retroactively_changes_a_past_order(): void
    {
        [$client, $product] = $this->makeClientAndProduct();
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(OrderForm::class)
            ->set('client_id', $client->id)
            ->set('product_id', $product->id)
            ->set('price', 100)
            ->set('purchase_date', '2026-08-01')
            ->set('expiry_date', '2027-08-01')
            ->call('save');

        $order = Order::where('client_id', $client->id)->first();
        $this->assertSame(5.0, (float) $order->cashback_amount);

        $product->update(['cashback_value' => 50]);
        $order->refresh();

        $this->assertSame(5.0, (float) $order->cashback_amount);
    }

    public function test_cashback_amount_is_locked_once_already_rewarded(): void
    {
        [$client, $product] = $this->makeClientAndProduct();
        $admin = $this->admin();

        $order = Order::create([
            'client_id' => $client->id,
            'product_id' => $product->id,
            'price' => 100,
            'purchase_date' => '2026-08-01',
            'expiry_date' => '2027-08-01',
            'status' => 'active',
            'currency' => 'TND',
            'warranty_start_mode' => 'purchase_date',
            'cashback_enabled_snapshot' => true,
            'cashback_type_snapshot' => 'fixed',
            'cashback_value_snapshot' => 5,
            'cashback_amount' => 5,
            'cashback_rewarded' => true,
            'cashback_rewarded_at' => now(),
        ])->refresh();

        Livewire::actingAs($admin)
            ->test(OrderForm::class, ['order' => $order])
            ->set('cashback_value', 999)
            ->set('cashback_note', 'Updated note only')
            ->call('save');

        $order->refresh();

        $this->assertSame(5.0, (float) $order->cashback_amount);
        $this->assertSame('Updated note only', $order->cashback_note);
    }
}
