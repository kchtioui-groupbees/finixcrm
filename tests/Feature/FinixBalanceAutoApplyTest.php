<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\ClientBalanceTransaction;
use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use App\Services\DashboardStatsService;
use App\Services\FinixBalanceAutoApplyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Coverage for the new "Finix balance auto-applies to unpaid orders" rule.
 * This reverses the previous rule (enforced by the now-deleted
 * CreditNeverAutoAppliedTest) at the user's explicit request — available
 * balance now applies automatically, oldest unpaid order first, subject
 * to admin settings, while pending cashback and revenue accounting stay
 * exactly as strict as before.
 */
class FinixBalanceAutoApplyTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => User::ROLE_OWNER]);
    }

    private function makeClient(array $overrides = []): Client
    {
        return Client::create(array_merge([
            'name' => 'Ahmed', 'currency' => 'TND', 'credit_balance' => 0,
        ], $overrides));
    }

    private function makeOrder(Client $client, float $price, string $purchaseDate, array $overrides = []): Order
    {
        $product = Product::create(['name' => 'ChatGPT Plus', 'slug' => 'cgpt-' . uniqid(), 'is_active' => true]);

        return Order::create(array_merge([
            'client_id' => $client->id,
            'product_id' => $product->id,
            'price' => $price,
            'purchase_date' => $purchaseDate,
            'expiry_date' => now()->addYear()->format('Y-m-d'),
            'status' => 'active',
            'currency' => 'TND',
        ], $overrides));
    }

    private function service(): FinixBalanceAutoApplyService
    {
        return app(FinixBalanceAutoApplyService::class);
    }

    // ── Balance greater than remaining ──────────────────────────────────

    public function test_balance_greater_than_remaining_amount_fully_pays_the_order_and_keeps_the_rest(): void
    {
        $client = $this->makeClient();
        $order = $this->makeOrder($client, 45.00, '2026-08-01');

        ClientBalanceTransaction::create([
            'client_id' => $client->id, 'amount' => 45.53, 'type' => 'cashback_reward',
            'description' => 'Reward', 'currency' => 'TND',
        ]);
        $client->refreshBalance();

        $this->service()->applyToUnpaidOrders($client->fresh());

        $order->refresh();
        $this->assertSame(45.00, round((float) $order->paid_amount, 2));
        $this->assertSame(0.0, round((float) $order->pending_amount, 2));
        $this->assertSame('completed', $order->status);
        $this->assertSame(0.53, round((float) $client->fresh()->credit_balance, 2));
    }

    // ── Balance less than remaining (partial) ───────────────────────────

    public function test_balance_less_than_remaining_amount_applies_only_what_is_available(): void
    {
        $client = $this->makeClient();
        $order = $this->makeOrder($client, 45.00, '2026-08-01');

        ClientBalanceTransaction::create([
            'client_id' => $client->id, 'amount' => 20.00, 'type' => 'cashback_reward',
            'description' => 'Reward', 'currency' => 'TND',
        ]);
        $client->refreshBalance();

        $this->service()->applyToUnpaidOrders($client->fresh());

        $order->refresh();
        $this->assertSame(20.00, round((float) $order->paid_amount, 2));
        $this->assertSame(25.00, round((float) $order->pending_amount, 2));
        $this->assertSame('partially_paid', $order->status);
        $this->assertSame(0.0, round((float) $client->fresh()->credit_balance, 2));
    }

    // ── Partial payment by balance (mixed: some cash, some balance) ────

    public function test_balance_tops_up_an_order_that_already_has_a_partial_cash_payment(): void
    {
        $client = $this->makeClient();
        $order = $this->makeOrder($client, 100.00, '2026-08-01');

        \App\Models\Payment::create([
            'client_id' => $client->id, 'order_id' => $order->id, 'amount' => 60,
            'payment_method' => 'especes', 'status' => 'completed', 'payment_date' => '2026-08-02',
            'type' => 'specific_order', 'currency' => 'TND',
        ]);
        app(\App\Services\PaymentAllocationService::class)->reallocateForClient($client->id);

        ClientBalanceTransaction::create([
            'client_id' => $client->id, 'amount' => 40.00, 'type' => 'refund',
            'description' => 'Avoir', 'currency' => 'TND',
        ]);
        $client->fresh()->refreshBalance();

        $this->service()->applyToUnpaidOrders($client->fresh());

        $order->refresh();
        $this->assertSame(100.00, round((float) $order->paid_amount, 2));
        $this->assertSame('completed', $order->status);
    }

    // ── Multiple unpaid orders + oldest-first priority ──────────────────

    public function test_multiple_unpaid_orders_are_covered_oldest_first(): void
    {
        $client = $this->makeClient();
        $oldest = $this->makeOrder($client, 50.00, '2026-08-01');
        $middle = $this->makeOrder($client, 30.00, '2026-08-05');
        $newest = $this->makeOrder($client, 20.00, '2026-08-10');

        ClientBalanceTransaction::create([
            'client_id' => $client->id, 'amount' => 70.00, 'type' => 'cashback_reward',
            'description' => 'Reward', 'currency' => 'TND',
        ]);
        $client->refreshBalance();

        $this->service()->applyToUnpaidOrders($client->fresh());

        $oldest->refresh();
        $middle->refresh();
        $newest->refresh();

        $this->assertSame('completed', $oldest->status);
        $this->assertSame(50.00, round((float) $oldest->paid_amount, 2));

        $this->assertSame('partially_paid', $middle->status);
        $this->assertSame(20.00, round((float) $middle->paid_amount, 2));

        $this->assertSame(0.0, round((float) $newest->paid_amount, 2));
        $this->assertNotSame('completed', $newest->status);

        $this->assertSame(0.0, round((float) $client->fresh()->credit_balance, 2));
    }

    // ── Pending cashback is never used ───────────────────────────────────

    public function test_pending_cashback_is_never_auto_applied(): void
    {
        $client = $this->makeClient();

        // Order whose cashback hasn't been rewarded yet (still pending) —
        // no ledger entry exists for it, so there is nothing to draw from.
        $this->makeOrder($client, 100.00, '2026-08-01', [
            'cashback_enabled_snapshot' => true,
            'cashback_type_snapshot' => 'fixed',
            'cashback_value_snapshot' => 10,
            'cashback_amount' => 10,
            'cashback_rewarded' => false,
        ]);

        $unpaidOrder = $this->makeOrder($client, 30.00, '2026-08-02');

        $this->assertSame(10.0, $client->fresh()->cashback_pending);
        $this->assertSame(0.0, (float) $client->fresh()->credit_balance);

        $this->service()->applyToUnpaidOrders($client->fresh());

        $unpaidOrder->refresh();
        $this->assertSame(0.0, round((float) $unpaidOrder->paid_amount, 2));
        $this->assertSame(30.00, round((float) $unpaidOrder->pending_amount, 2));
    }

    public function test_the_same_amount_is_never_shown_as_both_available_and_pending(): void
    {
        $client = $this->makeClient();

        $this->makeOrder($client, 100.00, '2026-08-01', [
            'cashback_enabled_snapshot' => true,
            'cashback_type_snapshot' => 'fixed',
            'cashback_value_snapshot' => 10,
            'cashback_amount' => 10,
            'cashback_rewarded' => false,
        ]);

        $fresh = $client->fresh();
        // Pending cashback (10) and available balance (0) never overlap.
        $this->assertSame(10.0, $fresh->cashback_pending);
        $this->assertSame(0.0, $fresh->cashback_available);
    }

    // ── No double debit ───────────────────────────────────────────────

    public function test_running_auto_apply_twice_never_applies_the_same_credit_twice(): void
    {
        $client = $this->makeClient();
        $order = $this->makeOrder($client, 45.00, '2026-08-01');

        ClientBalanceTransaction::create([
            'client_id' => $client->id, 'amount' => 45.00, 'type' => 'cashback_reward',
            'description' => 'Reward', 'currency' => 'TND',
        ]);
        $client->refreshBalance();

        $this->service()->applyToUnpaidOrders($client->fresh());
        $firstPaid = $order->fresh()->paid_amount;

        // Calling it again — nothing left to apply, must be a true no-op.
        $applied = $this->service()->applyToUnpaidOrders($client->fresh());

        $this->assertSame([], $applied);
        $this->assertSame($firstPaid, $order->fresh()->paid_amount);
        $this->assertSame(0.0, round((float) $client->fresh()->credit_balance, 2));

        // Only one 'usage' transaction was ever created for this order.
        $this->assertSame(
            1,
            ClientBalanceTransaction::where('client_id', $client->id)
                ->where('type', 'usage')
                ->where('reference_id', $order->id)
                ->count()
        );
    }

    public function test_reallocation_never_reapplies_an_already_applied_automatic_credit(): void
    {
        $client = $this->makeClient();
        $order = $this->makeOrder($client, 45.00, '2026-08-01');

        ClientBalanceTransaction::create([
            'client_id' => $client->id, 'amount' => 45.00, 'type' => 'cashback_reward',
            'description' => 'Reward', 'currency' => 'TND',
        ]);
        $client->refreshBalance();

        $this->service()->applyToUnpaidOrders($client->fresh());
        $paidAfterFirst = $order->fresh()->paid_amount;

        // A totally unrelated reallocation run later (e.g. a new order elsewhere).
        app(\App\Services\PaymentAllocationService::class)->reallocateForClient($client->id);

        $this->assertSame($paidAfterFirst, $order->fresh()->paid_amount);
    }

    /**
     * Explicit stress test against double-debits and runaway loops:
     * hammer applyToUnpaidOrders() repeatedly (simulating e.g. a retried
     * request or an overlapping trigger from both a payment confirmation
     * and a manual adjustment landing close together) and prove the total
     * ever applied never exceeds the credit that was ever actually
     * granted, exactly one 'usage' transaction exists per order, and the
     * client's ledger sum stays internally consistent throughout.
     */
    public function test_repeated_calls_never_cause_a_double_debit_or_diverge_the_ledger(): void
    {
        $client = $this->makeClient();
        $orderA = $this->makeOrder($client, 20.00, '2026-08-01');
        $orderB = $this->makeOrder($client, 30.00, '2026-08-05');

        ClientBalanceTransaction::create([
            'client_id' => $client->id, 'amount' => 35.00, 'type' => 'cashback_reward',
            'description' => 'Reward', 'currency' => 'TND',
        ]);
        $client->refreshBalance();

        // Call it 10 times in a row — as if triggered redundantly from
        // several places (payment confirm + manual adjustment + a retry).
        for ($i = 0; $i < 10; $i++) {
            $this->service()->applyToUnpaidOrders($client->fresh());
        }

        $orderA->refresh();
        $orderB->refresh();

        $this->assertSame('completed', $orderA->status);
        $this->assertSame(20.00, round((float) $orderA->paid_amount, 2));

        $this->assertSame('partially_paid', $orderB->status);
        $this->assertSame(15.00, round((float) $orderB->paid_amount, 2));

        // Never more than one automatic 'usage' transaction per order.
        $this->assertSame(
            1,
            ClientBalanceTransaction::where('client_id', $client->id)
                ->where('type', 'usage')->where('reference_id', $orderA->id)->count()
        );
        $this->assertSame(
            1,
            ClientBalanceTransaction::where('client_id', $client->id)
                ->where('type', 'usage')->where('reference_id', $orderB->id)->count()
        );

        // Total applied (20 + 15 = 35) exactly matches the credit granted —
        // never more, proving no double-debit occurred across 10 calls.
        $totalUsage = abs((float) ClientBalanceTransaction::where('client_id', $client->id)
            ->where('type', 'usage')->sum('amount'));
        $this->assertSame(35.00, round($totalUsage, 2));

        // The cached balance still matches a fresh re-sum of the ledger —
        // no drift introduced by the repeated calls.
        $ledgerSum = (float) ClientBalanceTransaction::where('client_id', $client->id)->sum('amount');
        $this->assertSame(round($ledgerSum, 2), round((float) $client->fresh()->credit_balance, 2));
        $this->assertSame(0.0, round((float) $client->fresh()->credit_balance, 2));
    }

    /**
     * Bounds the cashback-release cascade: even with a long chain of
     * orders that each release the next one's funding, the loop
     * terminates (MAX_PASSES) and every order is settled at most once —
     * no infinite loop, no order double-paid.
     */
    public function test_a_long_cascade_chain_terminates_without_double_paying_any_order(): void
    {
        $client = $this->makeClient();
        $orders = [];

        // 5 orders, each 10 TND, each carrying 10 TND of its own pending
        // cashback — paying order N releases exactly enough to pay order N+1.
        for ($i = 0; $i < 5; $i++) {
            $orders[] = $this->makeOrder($client, 10.00, sprintf('2026-08-%02d', $i + 1), [
                'cashback_enabled_snapshot' => true,
                'cashback_type_snapshot' => 'fixed',
                'cashback_value_snapshot' => 10,
                'cashback_amount' => 10,
                'cashback_rewarded' => false,
            ]);
        }

        // Seed only the first order's worth of real, available credit —
        // everything after order 1 is funded purely by the cascade.
        ClientBalanceTransaction::create([
            'client_id' => $client->id, 'amount' => 10.00, 'type' => 'manual_adjustment',
            'description' => 'Seed credit', 'currency' => 'TND',
        ]);
        $client->refreshBalance();

        $this->service()->applyToUnpaidOrders($client->fresh());

        foreach ($orders as $order) {
            $order->refresh();
            $this->assertSame('completed', $order->status);
            $this->assertSame(10.00, round((float) $order->paid_amount, 2));
        }

        // Exactly one automatic 'usage' transaction per order — no order
        // was paid twice by the cascade looping back over it.
        foreach ($orders as $order) {
            $this->assertSame(
                1,
                ClientBalanceTransaction::where('client_id', $client->id)
                    ->where('type', 'usage')->where('reference_id', $order->id)->count()
            );
        }

        // The last order's own released cashback (10 TND) has no further
        // unpaid order left to fund, so it correctly stays available —
        // it is NOT lost, and it was NOT double-applied to any order.
        $this->assertSame(10.00, round((float) $client->fresh()->credit_balance, 2));
    }

    // ── History correctly recorded ──────────────────────────────────────

    public function test_application_creates_a_ledger_transaction_and_allocation_with_the_expected_message(): void
    {
        $client = $this->makeClient();
        $order = $this->makeOrder($client, 45.00, '2026-08-01');

        ClientBalanceTransaction::create([
            'client_id' => $client->id, 'amount' => 45.00, 'type' => 'cashback_reward',
            'description' => 'Reward', 'currency' => 'TND',
        ]);
        $client->refreshBalance();

        $applied = $this->service()->applyToUnpaidOrders($client->fresh());

        $this->assertCount(1, $applied);
        $this->assertSame($order->id, $applied[0]['order_id']);
        $this->assertSame(45.00, $applied[0]['amount']);

        $txn = ClientBalanceTransaction::find($applied[0]['transaction_id']);
        $this->assertNotNull($txn);
        $this->assertSame('usage', $txn->type);
        $this->assertSame(-45.00, round((float) $txn->amount, 2));
        $this->assertNull($txn->created_by); // system/automatic, not an admin action
        $this->assertSame('order', $txn->reference_type);
        $this->assertSame($order->id, $txn->reference_id);
        $this->assertStringContainsString('appliqués automatiquement à la commande #' . $order->id, $txn->description);

        $allocation = \App\Models\PaymentAllocation::where('balance_transaction_id', $txn->id)->first();
        $this->assertNotNull($allocation);
        $this->assertSame($order->id, $allocation->order_id);
        $this->assertSame(45.00, round((float) $allocation->amount, 2));
    }

    // ── Revenue is never increased by a balance allocation ──────────────

    public function test_auto_applied_balance_never_counts_as_revenue(): void
    {
        $client = $this->makeClient();
        $order = $this->makeOrder($client, 45.00, now()->format('Y-m-d'));

        ClientBalanceTransaction::create([
            'client_id' => $client->id, 'amount' => 45.00, 'type' => 'cashback_reward',
            'description' => 'Reward', 'currency' => 'TND',
        ]);
        $client->refreshBalance();

        $this->service()->applyToUnpaidOrders($client->fresh());
        $order->refresh();
        $this->assertSame('completed', $order->status);

        $stats = app(DashboardStatsService::class);
        [$from, $to] = $stats->resolvePeriod('today');
        $periodStats = $stats->getPeriodStats($from, $to);

        $this->assertArrayNotHasKey('TND', $periodStats['revenue_by_currency']);
        $this->assertSame(0, \App\Models\Payment::where('client_id', $client->id)->count());
    }

    // ── Admin settings gate the feature ─────────────────────────────────

    public function test_auto_apply_does_nothing_when_disabled(): void
    {
        Setting::set('finix_balance.auto_apply_enabled', false);

        $client = $this->makeClient();
        $order = $this->makeOrder($client, 45.00, '2026-08-01');

        ClientBalanceTransaction::create([
            'client_id' => $client->id, 'amount' => 45.00, 'type' => 'cashback_reward',
            'description' => 'Reward', 'currency' => 'TND',
        ]);
        $client->refreshBalance();

        $applied = $this->service()->applyToUnpaidOrders($client->fresh());

        $this->assertSame([], $applied);
        $this->assertSame(0.0, round((float) $order->fresh()->paid_amount, 2));
    }

    public function test_auto_apply_does_nothing_when_old_unpaid_orders_toggle_is_off(): void
    {
        Setting::set('finix_balance.auto_apply_to_old_orders', false);

        $client = $this->makeClient();
        $order = $this->makeOrder($client, 45.00, '2026-08-01');

        ClientBalanceTransaction::create([
            'client_id' => $client->id, 'amount' => 45.00, 'type' => 'cashback_reward',
            'description' => 'Reward', 'currency' => 'TND',
        ]);
        $client->refreshBalance();

        $applied = $this->service()->applyToUnpaidOrders($client->fresh());

        $this->assertSame([], $applied);
        $this->assertSame(0.0, round((float) $order->fresh()->paid_amount, 2));
    }

    public function test_only_admin_configured_credit_types_are_eligible(): void
    {
        Setting::set('finix_balance.auto_apply_allowed_types', ['cashback_reward']);

        $client = $this->makeClient();
        $order = $this->makeOrder($client, 45.00, '2026-08-01');

        // Only an 'overpayment' credit exists — not in the allowed list.
        ClientBalanceTransaction::create([
            'client_id' => $client->id, 'amount' => 45.00, 'type' => 'overpayment',
            'description' => 'Overpaid', 'currency' => 'TND',
        ]);
        $client->refreshBalance();

        $applied = $this->service()->applyToUnpaidOrders($client->fresh());

        $this->assertSame([], $applied);
        $this->assertSame(0.0, round((float) $order->fresh()->paid_amount, 2));
    }

    // ── Reversible only by a controlled admin action ────────────────────

    public function test_admin_can_reverse_an_automatic_application(): void
    {
        $client = $this->makeClient();
        $order = $this->makeOrder($client, 45.00, '2026-08-01');
        $admin = $this->admin();

        ClientBalanceTransaction::create([
            'client_id' => $client->id, 'amount' => 45.00, 'type' => 'cashback_reward',
            'description' => 'Reward', 'currency' => 'TND',
        ]);
        $client->refreshBalance();

        $applied = $this->service()->applyToUnpaidOrders($client->fresh());
        $txn = ClientBalanceTransaction::find($applied[0]['transaction_id']);

        $this->service()->reverseApplication($txn, $admin);

        $order->refresh();
        $this->assertSame(0.0, round((float) $order->paid_amount, 2));
        $this->assertSame(45.00, round((float) $order->pending_amount, 2));
        $this->assertSame('pending', $order->status);
        $this->assertSame(45.00, round((float) $client->fresh()->credit_balance, 2));

        // The original transaction and allocation are never deleted.
        $this->assertNotNull(ClientBalanceTransaction::find($txn->id));

        // History stays complete: both the original application and the
        // reversal are separately visible in the ledger.
        $this->assertSame(
            2,
            ClientBalanceTransaction::where('client_id', $client->id)
                ->whereIn('reference_id', [$txn->id])
                ->orWhere('id', $txn->id)
                ->count()
        );

        // A reversal is a pure ledger/allocation operation — it never
        // creates a Payment row, so revenue stays unaffected either way.
        $stats = app(\App\Services\DashboardStatsService::class);
        [$from, $to] = $stats->resolvePeriod('today');
        $periodStats = $stats->getPeriodStats($from, $to);
        $this->assertArrayNotHasKey('TND', $periodStats['revenue_by_currency']);
    }

    public function test_admin_can_reverse_via_the_client_show_action(): void
    {
        $client = $this->makeClient();
        $order = $this->makeOrder($client, 45.00, '2026-08-01');
        $admin = $this->admin();

        ClientBalanceTransaction::create([
            'client_id' => $client->id, 'amount' => 45.00, 'type' => 'cashback_reward',
            'description' => 'Reward', 'currency' => 'TND',
        ]);
        $client->refreshBalance();

        $applied = $this->service()->applyToUnpaidOrders($client->fresh());

        \Livewire\Livewire::actingAs($admin)
            ->test(\App\Livewire\Clients\ClientShow::class, ['client' => $client->fresh()])
            ->call('reverseAutoApplication', $applied[0]['transaction_id']);

        $order->refresh();
        $this->assertSame(0.0, round((float) $order->paid_amount, 2));
        $this->assertSame(45.00, round((float) $client->fresh()->credit_balance, 2));
    }

    public function test_reversal_cannot_target_a_manual_credit_application(): void
    {
        $client = $this->makeClient();
        $order = $this->makeOrder($client, 45.00, '2026-08-01');
        $admin = $this->admin();

        ClientBalanceTransaction::create([
            'client_id' => $client->id, 'amount' => 45.00, 'type' => 'cashback_reward',
            'description' => 'Reward', 'currency' => 'TND',
        ]);
        $client->refreshBalance();

        \Livewire\Livewire::actingAs($admin)
            ->test(\App\Livewire\Clients\ClientTransactions::class, ['client' => $client])
            ->set('selectedOrderId', $order->id)
            ->set('amountToApply', 45)
            ->call('applyCredit');

        $manualTxn = ClientBalanceTransaction::where('client_id', $client->id)->where('type', 'usage')->first();

        $this->expectException(\RuntimeException::class);
        $this->service()->reverseApplication($manualTxn, $admin);
    }

    // ── Cascading: paying an order off via balance can release its own cashback ──

    public function test_paying_an_order_off_via_balance_releases_its_own_pending_cashback_and_cascades(): void
    {
        $client = $this->makeClient();

        $orderA = $this->makeOrder($client, 50.00, '2026-08-01', [
            'cashback_enabled_snapshot' => true,
            'cashback_type_snapshot' => 'fixed',
            'cashback_value_snapshot' => 20,
            'cashback_amount' => 20,
            'cashback_rewarded' => false,
        ]);
        $orderB = $this->makeOrder($client, 15.00, '2026-08-05');

        ClientBalanceTransaction::create([
            'client_id' => $client->id, 'amount' => 50.00, 'type' => 'manual_adjustment',
            'description' => 'Goodwill', 'currency' => 'TND',
        ]);
        $client->refreshBalance();

        $this->service()->applyToUnpaidOrders($client->fresh());

        $orderA->refresh();
        $orderB->refresh();

        $this->assertSame('completed', $orderA->status);
        $this->assertTrue((bool) $orderA->cashback_rewarded);

        // The 20 TND cashback released by fully paying orderA cascaded to
        // cover orderB.
        $this->assertSame('completed', $orderB->status);
        $this->assertSame(15.00, round((float) $orderB->paid_amount, 2));
    }
}
