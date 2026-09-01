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
use App\Services\FinixBalanceAutoApplyService;
use App\Services\PaymentAllocationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * HISTORY: this file originally locked in a pre-commit audit finding that
 * PaymentAllocationService was silently auto-applying a client's credit to
 * any pending order with no admin action — a violation of the rule at the
 * time ("cashback/balance must only reduce what a client owes when an
 * admin explicitly applies it").
 *
 * The business rule was later explicitly REVERSED at the user's request:
 * "Le solde Finix disponible doit maintenant pouvoir s'appliquer
 * automatiquement aux commandes impayées." Per that same request, this
 * file is kept (not deleted) and rewritten to verify the CURRENT rule —
 * preserving the regression-coverage intent (a dedicated file proving
 * exactly how credit application behaves) rather than the specific
 * assertions, which would now be wrong. The comprehensive day-to-day
 * coverage (FIFO priority, partial application, pending-cashback
 * exclusion, no-double-debit, reversal, admin settings...) lives in
 * FinixBalanceAutoApplyTest — this file stays focused on the narrower
 * question its name asks: does credit auto-apply, does a source the admin
 * excluded still stay put, and does either ever conflict with an admin's
 * own explicit application?
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

    public function test_existing_cashback_credit_is_now_auto_applied_when_a_new_order_is_created(): void
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

        // Per the current rule, this now DOES apply automatically — the
        // order is created already fully covered by the client's balance.
        $this->assertSame(50.0, round((float) $order->paid_amount, 2));
        $this->assertSame(0.0, round((float) $order->pending_amount, 2));
        $this->assertSame('completed', $order->status);
        $this->assertSame(50.0, round((float) $client->fresh()->credit_balance, 2));
    }

    public function test_existing_credit_is_now_auto_applied_when_a_payment_is_confirmed_for_another_order(): void
    {
        [$client, $product] = $this->makeClientAndProduct();
        $admin = $this->admin();

        ClientBalanceTransaction::create([
            'client_id' => $client->id, 'amount' => 200, 'type' => 'refund',
            'description' => 'Credit note from a cancelled order', 'currency' => 'TND',
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

        // Confirming a payment reconciles the whole client, so the balance
        // sitting untouched since before this payment gets swept too —
        // oldest order first, then whatever is left over to the next one.
        $payment = Payment::create([
            'client_id' => $client->id, 'order_id' => $orderA->id, 'amount' => 40,
            'payment_method' => 'especes', 'status' => 'pending', 'payment_date' => '2026-08-03',
            'type' => 'specific_order', 'currency' => 'TND',
        ]);
        app(\App\Services\PaymentConfirmationService::class)->confirm($payment, $admin);

        $orderA->refresh();
        $orderB->refresh();

        // 40 cash + 60 balance on the oldest order, then 60 balance on the next.
        $this->assertSame(100.0, round((float) $orderA->paid_amount, 2));
        $this->assertSame(60.0, round((float) $orderB->paid_amount, 2));
        $this->assertSame(0.0, round((float) $orderB->pending_amount, 2));
        $this->assertSame(80.0, round((float) $client->fresh()->credit_balance, 2));
    }

    /**
     * The narrower question this file's name asks, in its still-true form:
     * a credit source the admin has excluded is never swept, however many
     * unpaid orders are sitting there and whatever else reconciles.
     */
    public function test_a_credit_type_the_admin_excluded_is_never_auto_applied(): void
    {
        \App\Models\Setting::set('finix_balance.auto_apply_allowed_types', ['cashback_reward']);

        [$client, $product] = $this->makeClientAndProduct();
        $admin = $this->admin();

        ClientBalanceTransaction::create([
            'client_id' => $client->id, 'amount' => 200, 'type' => 'refund',
            'description' => 'Credit note from a cancelled order', 'currency' => 'TND',
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

        $orderA->refresh();
        $orderB->refresh();

        // Only the 40 of real cash landed; the refund credit stayed put.
        $this->assertSame(40.0, round((float) $orderA->paid_amount, 2));
        $this->assertSame(0.0, round((float) $orderB->paid_amount, 2));
        $this->assertSame(60.0, round((float) $orderB->pending_amount, 2));
        $this->assertSame(200.0, round((float) $client->fresh()->credit_balance, 2));
        $this->assertSame(['refund' => 200.0], $client->fresh()->balance_breakdown);
        $this->assertSame(0.0, $client->fresh()->auto_apply_eligible_balance);
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
        $this->assertSame(80.0, round((float) $order->paid_amount, 2));
        $this->assertSame('paid', $order->payment_status);

        // Unrelated later activity triggers a reallocation for this client...
        $order2 = Order::create([
            'client_id' => $client->id, 'product_id' => $product->id, 'price' => 30,
            'purchase_date' => '2026-08-05', 'expiry_date' => '2027-08-05', 'status' => 'active', 'currency' => 'TND',
        ]);
        app(PaymentAllocationService::class)->reallocateForClient($client->id);

        // ...the admin's earlier, explicit credit application on $order must
        // still be there — not wiped, and never re-applied a second time.
        $order->refresh();
        $this->assertSame(80.0, round((float) $order->paid_amount, 2));
        $this->assertSame('paid', $order->payment_status);
        $this->assertSame(
            1,
            ClientBalanceTransaction::where('client_id', $client->id)
                ->where('type', 'usage')->where('reference_id', $order->id)->count()
        );
    }

    public function test_manual_adjustment_credit_is_now_auto_applied_to_pending_orders(): void
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

        $order->refresh();
        $this->assertSame(40.0, round((float) $order->paid_amount, 2));
        $this->assertSame(0.0, round((float) $order->pending_amount, 2));
        $this->assertSame('completed', $order->status);
    }

    /** The admin can still turn the new automatic behavior off entirely. */
    public function test_auto_apply_can_be_turned_off_to_restore_the_old_manual_only_behavior(): void
    {
        \App\Models\Setting::set('finix_balance.auto_apply_enabled', false);

        [$client, $product] = $this->makeClientAndProduct();
        $admin = $this->admin();

        ClientBalanceTransaction::create([
            'client_id' => $client->id, 'amount' => 100, 'type' => 'cashback_reward',
            'description' => 'Reward', 'currency' => 'TND',
        ]);
        $client->refreshBalance();

        Livewire::actingAs($admin)
            ->test(OrderForm::class)
            ->set('client_id', $client->id)
            ->set('product_id', $product->id)
            ->set('price', 50)
            ->set('purchase_date', '2026-08-01')
            ->set('expiry_date', '2027-08-01')
            ->call('save');

        $order = Order::where('client_id', $client->id)->first();

        $this->assertSame(0.0, round((float) $order->paid_amount, 2));
        $this->assertSame(50.0, round((float) $order->pending_amount, 2));
        $this->assertSame(100.0, round((float) $client->fresh()->credit_balance, 2));
    }
}
