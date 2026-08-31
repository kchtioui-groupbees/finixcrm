<?php

namespace Tests\Feature;

use App\Livewire\Clients\ClientShow;
use App\Models\Client;
use App\Models\ClientBalanceTransaction;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ClientShowTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => User::ROLE_OWNER]);
    }

    private function makeClientWithOrder(array $orderOverrides = []): Order
    {
        $client = Client::create([
            'name' => 'Ahmed Chiheb',
            'email' => 'ahmed@example.com',
            'finix_email' => 'achiheb@finix.tn',
            'phone' => '92871752',
            'currency' => 'TND',
            'credit_balance' => 0,
            'status' => 'active',
        ]);

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
            'purchase_date' => '2026-08-01',
            'expiry_date' => '2027-08-01',
            'status' => 'active',
            'currency' => 'TND',
            'warranty_enabled' => true,
            'warranty_end_date' => '2027-08-01',
        ], $orderOverrides));
    }

    public function test_client_360_page_displays_the_required_summary_fields(): void
    {
        $order = $this->makeClientWithOrder();
        $client = $order->client;
        $admin = $this->admin();

        Payment::create([
            'client_id' => $client->id,
            'order_id' => $order->id,
            'amount' => 30,
            'payment_method' => 'especes',
            'status' => 'completed',
            'payment_date' => '2026-08-01',
            'type' => 'specific_order',
            'currency' => 'TND',
        ]);

        Livewire::actingAs($admin)
            ->test(ClientShow::class, ['client' => $client])
            ->assertSee('Ahmed Chiheb')
            ->assertSee('ahmed@example.com')
            ->assertSee('achiheb@finix.tn')
            ->assertSee('92871752');
    }

    public function test_all_client_360_tabs_render_without_error(): void
    {
        $order = $this->makeClientWithOrder([
            'cashback_enabled_snapshot' => true,
            'cashback_type_snapshot' => 'fixed',
            'cashback_value_snapshot' => 5,
            'cashback_amount' => 5,
            'cashback_rewarded' => true,
            'cashback_rewarded_at' => now(),
        ]);
        $client = $order->client;
        $admin = $this->admin();

        ClientBalanceTransaction::create([
            'client_id' => $client->id,
            'type' => 'cashback_reward',
            'amount' => 5,
            'description' => 'Cashback for order #' . $order->id,
        ]);

        foreach (['overview', 'orders', 'transactions', 'warranty', 'cashback', 'balance', 'notes', 'history'] as $tab) {
            Livewire::actingAs($admin)
                ->test(ClientShow::class, ['client' => $client])
                ->call('setTab', $tab)
                ->assertOk();
        }
    }

    public function test_cashback_tab_lists_orders_with_cashback(): void
    {
        $order = $this->makeClientWithOrder([
            'cashback_enabled_snapshot' => true,
            'cashback_type_snapshot' => 'fixed',
            'cashback_value_snapshot' => 5,
            'cashback_amount' => 5,
            'cashback_rewarded' => true,
            'cashback_rewarded_at' => now(),
            'cashback_note' => 'Promo été',
        ]);
        $client = $order->client;
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(ClientShow::class, ['client' => $client])
            ->call('setTab', 'cashback')
            ->assertSee('Promo été');
    }

    public function test_history_tab_lists_order_and_payment_events(): void
    {
        $order = $this->makeClientWithOrder();
        $client = $order->client;
        $admin = $this->admin();

        Payment::create([
            'client_id' => $client->id,
            'order_id' => $order->id,
            'amount' => 30,
            'payment_method' => 'especes',
            'status' => 'completed',
            'payment_date' => '2026-08-01',
            'type' => 'specific_order',
            'currency' => 'TND',
        ]);

        Livewire::actingAs($admin)
            ->test(ClientShow::class, ['client' => $client])
            ->call('setTab', 'history')
            ->assertSee(__('Order created'))
            ->assertSee(__('Payment recorded'));
    }

    public function test_adding_an_internal_note_never_deletes_prior_notes(): void
    {
        $order = $this->makeClientWithOrder();
        $client = $order->client;
        $client->update(['notes' => 'Original note kept forever']);
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(ClientShow::class, ['client' => $client])
            ->set('newNote', 'A brand new note')
            ->call('addNote');

        $client->refresh();
        $this->assertStringContainsString('Original note kept forever', $client->notes);
        $this->assertStringContainsString('A brand new note', $client->notes);
    }
}
