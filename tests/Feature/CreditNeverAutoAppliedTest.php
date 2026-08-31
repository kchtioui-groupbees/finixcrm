<?php

namespace Tests\Feature;

use App\Livewire\Clients\ClientTransactions;
use App\Livewire\Orders\OrderForm;
use App\Models\Client;
use App\Models\ClientBalanceTransaction;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use App\Services\PaymentAllocationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Regression coverage for the pre-commit audit finding that
 * PaymentAllocationService::reallocateForClient() was silently applying a
 * client's available credit (cashback rewards, overpayments, manual
 * adjustments, refunds) to any pending order — with no admin action —
 * every time an order was created or a payment was confirmed. That
 * violates the rule that cashback/balance must only reduce what a client
 * owes when an admin explicitly applies it.
 */
class CreditNeverAutoAppliedTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => User::ROLE_OWNER]);
    }

    private function makeClientAndProduct(float $creditBalance = 0): array
    {
        $client = Client::create(['name' => 'Ahmed', 'currency' => 'TND', 'credit_balance' => $creditBalance]);
        $product = Product::create(['name' => 'ChatGPT Plus', 'slug' => 'cgpt-' . uniqid(), 'is_active' => true]);

        return [$client, $product];
    }

    public function test_existing_cashback_credit_is_not_auto_applied_when_a_new_order_is_created(): void
    {
        [$client, $product] = $this->makeClientAndProduct();
        $admin = $this->admin();

        // Client has an unapplied cashback reward sitting in their wallet.
        ClientBalanceTransaction::create([
            'client_id' => $client->id, 'amount' => 100, 'type' => 'cashback_reward',
            'description' => 'Reward from a previous order', 'currency' => 'TND',
        ]);
        $client->refreshBalance();
        $this->assertSame(100.0, (float) $client->credit_balance);

        Livewire::actingAs($admin)
            ->test(OrderForm::class)
            ->set('client_id', $client->id)
            ->set('product_id', $product->id)
            ->set('price', 50)
            ->set('purchase_date', '2026-08-01')
            ->set('expiry_date', '2027-08-01')
            ->call('save');

        $order = Order::where('client_id', $client->id)->first();

        $this->assertSame(0.0, (float) $order->paid_amount);
        $this->assertSame(50.0, (float) $order->pending_amount);
        $this->assertNotSame('completed', $order->status);
        // The cashback credit is still sitting there, untouched.
        $this->assertSame(100.0, (float) $client->fresh()->credit_balance);
    }

    public function test_existing_credit_is_not_auto_applied_when_a_payment_is_confirmed_for_another_order(): void
    {
        [$client, $product] = $this->makeClientAndProduct();
        $admin = $this->admin();

        ClientBalanceTransaction::create([
            'client_id' => $client->id, 'amount' => 200, 'type' => 'overpayment',
            'description' => 'Excess from a prior payment', 'currency' => 'TND',
        ]);
        $client->refreshBalance();

        $orderA = Order::create([
            'client_id' => $client->id, 'product_id' => $product->id, 'price' => 100,
            'purchase_date' => '2026-08-01', 'expiry_date' => '2027-08-01', 'status' => 'active', 'currency' => 'TND',
        ]);
        $orderB = Order::create([
            'client_id' => $client->id, 'product_id' => $product->id, 'price' => 60,
            'purchase_date' => '2026-08-02', 'expiry_date' => '2027-08-02', 'status' => 'active', 'currency' => 'TND',
        ]);

        $payment = Payment::create([
            'client_id' => $client->id, 'order_id' => $orderA->id, 'amount' => 40,
            'payment_method' => 'especes', 'status' => 'pending', 'payment_date' => '2026-08-03',
            'type' => 'specific_order', 'currency' => 'TND',
        ]);
        app(\App\Services\PaymentConfirmationService::class)->confirm($payment, $admin);

        $orderB->refresh();

        // orderB must still be fully unpaid — the client's leftover credit
        // (200 TND) must never have been silently used to cover it.
        $this->assertSame(0.0, (float) $orderB->paid_amount);
        $this->assertSame(60.0, (float) $orderB->pending_amount);
    }

    public function test_explicitly_applied_credit_survives_a_later_reallocation(): void
    {
        [$client, $product] = $this->makeClientAndProduct();
        $admin = $this->admin();

        ClientBalanceTransaction::create([
            'client_id' => $client->id, 'amount' => 80, 'type' => 'cashback_reward',
            'description' => 'Reward', 'currency' => 'TND',
        ]);
        $client->refreshBalance();

        $order = Order::create([
            'client_id' => $client->id, 'product_id' => $product->id, 'price' => 80,
            'purchase_date' => '2026-08-01', 'expiry_date' => '2027-08-01', 'status' => 'active', 'currency' => 'TND',
        ]);

        // Admin explicitly applies the credit to this order via the real UI action.
        Livewire::actingAs($admin)
            ->test(ClientTransactions::class, ['client' => $client])
            ->set('selectedOrderId', $order->id)
            ->set('amountToApply', 80)
            ->call('applyCredit');

        $order->refresh();
        $this->assertSame(80.0, (float) $order->paid_amount);
        $this->assertSame('paid', $order->payment_status);

        // Unrelated later activity triggers a reallocation for this client...
        $order2 = Order::create([
            'client_id' => $client->id, 'product_id' => $product->id, 'price' => 30,
            'purchase_date' => '2026-08-05', 'expiry_date' => '2027-08-05', 'status' => 'active', 'currency' => 'TND',
        ]);
        app(PaymentAllocationService::class)->reallocateForClient($client->id);

        // ...the admin's earlier, explicit credit application on $order must
        // still be there — not wiped and not silently re-applied elsewhere.
        $order->refresh();
        $order2->refresh();
        $this->assertSame(80.0, (float) $order->paid_amount);
        $this->assertSame('paid', $order->payment_status);
        $this->assertSame(0.0, (float) $order2->paid_amount);
    }

    public function test_manual_adjustment_credit_is_not_auto_applied_to_pending_orders(): void
    {
        [$client, $product] = $this->makeClientAndProduct();
        $admin = $this->admin();

        $order = Order::create([
            'client_id' => $client->id, 'product_id' => $product->id, 'price' => 40,
            'purchase_date' => '2026-08-01', 'expiry_date' => '2027-08-01', 'status' => 'active', 'currency' => 'TND',
        ]);

        Livewire::actingAs($admin)
            ->test(ClientTransactions::class, ['client' => $client])
            ->set('manualType', 'manual_adjustment')
            ->set('manualAmount', 40)
            ->set('manualDescription', 'Goodwill credit')
            ->call('manualAdjustment');

        app(PaymentAllocationService::class)->reallocateForClient($client->id);

        $order->refresh();
        $this->assertSame(0.0, (float) $order->paid_amount);
        $this->assertSame(40.0, (float) $order->pending_amount);
    }
}
