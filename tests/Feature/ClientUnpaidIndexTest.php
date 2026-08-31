<?php

namespace Tests\Feature;

use App\Livewire\Clients\ClientUnpaidIndex;
use App\Models\Client;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use App\Services\PaymentConfirmationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ClientUnpaidIndexTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => User::ROLE_OWNER]);
    }

    private function makeOrder(array $overrides = []): Order
    {
        $client = Client::create(array_merge([
            'name' => 'Ahmed Chiheb',
            'phone' => '92871752',
            'email' => 'ahmed@example.com',
            'currency' => 'TND',
            'credit_balance' => 0,
        ], $overrides['client'] ?? []));
        unset($overrides['client']);

        $product = Product::create([
            'name' => 'ChatGPT Plus',
            'slug' => 'chatgpt-plus-' . uniqid(),
            'is_active' => true,
        ]);

        return Order::create(array_merge([
            'client_id' => $client->id,
            'product_id' => $product->id,
            'price' => 100,
            'purchase_date' => '2026-08-01',
            'expiry_date' => '2027-08-01',
            'status' => 'active',
            'currency' => 'TND',
        ], $overrides));
    }

    public function test_fully_unpaid_order_is_listed_as_impaye(): void
    {
        $order = $this->makeOrder();
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(ClientUnpaidIndex::class)
            ->assertSee('Ahmed Chiheb')
            ->assertSee(__('Unpaid'));
    }

    public function test_pending_payments_never_count_toward_remaining_amount(): void
    {
        $order = $this->makeOrder();

        Payment::create([
            'client_id' => $order->client_id,
            'order_id' => $order->id,
            'amount' => 60,
            'payment_method' => 'virement_bancaire',
            'status' => 'pending',
            'payment_date' => '2026-08-05',
            'type' => 'specific_order',
            'currency' => 'TND',
        ]);

        $order->refresh();

        $this->assertSame(0.0, (float) $order->paid_amount);
        $this->assertSame(100.0, (float) $order->pending_amount);
    }

    public function test_order_with_pending_payment_is_flagged_as_pending_verification(): void
    {
        $order = $this->makeOrder();
        $admin = $this->admin();

        Payment::create([
            'client_id' => $order->client_id,
            'order_id' => $order->id,
            'amount' => 60,
            'payment_method' => 'virement_bancaire',
            'status' => 'pending',
            'payment_date' => '2026-08-05',
            'type' => 'specific_order',
            'currency' => 'TND',
        ]);

        Livewire::actingAs($admin)
            ->test(ClientUnpaidIndex::class)
            ->assertSee(__('Pending Verification'));
    }

    public function test_partially_paid_order_appears_in_partial_tab(): void
    {
        $order = $this->makeOrder();
        $admin = $this->admin();

        $payment = Payment::create([
            'client_id' => $order->client_id,
            'order_id' => $order->id,
            'amount' => 40,
            'payment_method' => 'especes',
            'status' => 'pending',
            'payment_date' => '2026-08-05',
            'type' => 'specific_order',
            'currency' => 'TND',
        ]);
        app(PaymentConfirmationService::class)->confirm($payment, $admin);
        $order->refresh();

        Livewire::actingAs($admin)
            ->test(ClientUnpaidIndex::class)
            ->call('setTab', 'partial')
            ->assertSee('Ahmed Chiheb')
            ->assertSee(__('Partial Payment'));
    }

    public function test_fully_paid_order_is_excluded_from_the_unpaid_list(): void
    {
        $order = $this->makeOrder();
        $admin = $this->admin();

        $payment = Payment::create([
            'client_id' => $order->client_id,
            'order_id' => $order->id,
            'amount' => 100,
            'payment_method' => 'especes',
            'status' => 'pending',
            'payment_date' => '2026-08-05',
            'type' => 'specific_order',
            'currency' => 'TND',
        ]);
        app(PaymentConfirmationService::class)->confirm($payment, $admin);

        Livewire::actingAs($admin)
            ->test(ClientUnpaidIndex::class)
            ->assertDontSee('Ahmed Chiheb');
    }

    public function test_rejected_payments_tab_lists_rejected_payments(): void
    {
        $order = $this->makeOrder();
        $admin = $this->admin();

        Payment::create([
            'client_id' => $order->client_id,
            'order_id' => $order->id,
            'amount' => 50,
            'payment_method' => 'virement_bancaire',
            'status' => 'rejected',
            'rejected_at' => now(),
            'payment_date' => '2026-08-05',
            'type' => 'specific_order',
            'currency' => 'TND',
        ]);

        Livewire::actingAs($admin)
            ->test(ClientUnpaidIndex::class)
            ->call('setTab', 'rejected')
            ->assertSee('Ahmed Chiheb');
    }

    public function test_unattached_payments_tab_lists_payments_without_a_client(): void
    {
        $order = $this->makeOrder();
        $admin = $this->admin();

        Payment::create([
            'client_id' => null,
            'order_id' => null,
            'amount' => 25,
            'payment_method' => 'especes',
            'reference' => 'ORPHAN-1',
            'status' => 'completed',
            'payment_date' => '2026-08-05',
            'type' => 'specific_order',
            'currency' => 'TND',
        ]);

        Livewire::actingAs($admin)
            ->test(ClientUnpaidIndex::class)
            ->call('setTab', 'unattached')
            ->assertSee('ORPHAN-1');
    }

    public function test_search_filters_by_client_name(): void
    {
        $order = $this->makeOrder();
        $this->makeOrder(['client' => ['name' => 'Someone Else', 'phone' => '99999999', 'email' => 'other@example.com']]);
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(ClientUnpaidIndex::class)
            ->set('search', 'Ahmed')
            ->assertSee('Ahmed Chiheb')
            ->assertDontSee('Someone Else');
    }
}
