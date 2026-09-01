<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\ClientBalanceTransaction;
use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use App\Services\FinixBalanceAutoApplyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Coverage for the "Available Finix Balance" story: the client can now see
 * WHICH credit source their balance came from, how much of it auto-apply
 * may draw on right now, and — in plain language — why the rest is sitting
 * still.
 *
 * The ledger is one shared pool (a debit row never records which credit it
 * drew from), so the split is attributed FIFO. These tests pin that
 * attribution down, prove it is exact rather than an approximation, and
 * prove it never loses or invents a millime.
 */
class FinixBalanceBreakdownTest extends TestCase
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

    /**
     * FIFO attribution is driven by created_at, so every ledger row here is
     * back-dated explicitly rather than left to land on the same second.
     */
    private function credit(Client $client, string $type, float $amount, string $at, string $currency = 'TND'): ClientBalanceTransaction
    {
        $txn = ClientBalanceTransaction::create([
            'client_id' => $client->id, 'amount' => $amount, 'type' => $type,
            'description' => ucfirst(str_replace('_', ' ', $type)), 'currency' => $currency,
        ]);

        $txn->forceFill(['created_at' => $at])->save();

        return $txn;
    }

    private function service(): FinixBalanceAutoApplyService
    {
        return app(FinixBalanceAutoApplyService::class);
    }

    /** @return list<string> */
    private function holdCodes(Client $client): array
    {
        return array_column($client->balance_hold_reasons, 'code');
    }

    /** @return array{code:string,amount:?float,message:string,type:?string} */
    private function holdReason(Client $client, string $code): array
    {
        $match = array_values(array_filter(
            $client->balance_hold_reasons,
            fn ($reason) => $reason['code'] === $code
        ));

        $this->assertCount(1, $match, "expected exactly one '{$code}' hold reason");

        return $match[0];
    }

    // ── FIFO attribution ────────────────────────────────────────────────

    public function test_a_debit_consumes_the_oldest_credit_first(): void
    {
        $client = $this->makeClient();

        $this->credit($client, 'cashback_reward', 10.000, '2026-08-01 09:00:00');
        $this->credit($client, 'overpayment', 20.000, '2026-08-02 09:00:00');
        $this->credit($client, 'usage', -15.000, '2026-08-03 09:00:00');
        $client->refreshBalance();

        // The 10 of cashback went first, then 5 of the overpayment.
        $this->assertSame(['overpayment' => 15.0], $client->fresh()->balance_breakdown);
        $this->assertSame(15.0, (float) $client->fresh()->credit_balance);
    }

    public function test_a_negative_manual_adjustment_spends_credit_just_like_a_usage(): void
    {
        $client = $this->makeClient();

        $this->credit($client, 'cashback_reward', 10.000, '2026-08-01 09:00:00');
        $this->credit($client, 'refund', 20.000, '2026-08-02 09:00:00');
        $this->credit($client, 'manual_adjustment', -12.000, '2026-08-03 09:00:00');
        $client->refreshBalance();

        $this->assertSame(['refund' => 18.0], $client->fresh()->balance_breakdown);
    }

    public function test_the_breakdown_always_sums_back_to_the_credit_balance(): void
    {
        $client = $this->makeClient();

        $this->credit($client, 'cashback_reward', 12.500, '2026-08-01 09:00:00');
        $this->credit($client, 'refund', 30.000, '2026-08-02 09:00:00');
        $this->credit($client, 'usage', -20.250, '2026-08-03 09:00:00');
        $this->credit($client, 'manual_adjustment', 5.750, '2026-08-04 09:00:00');
        $this->credit($client, 'manual_adjustment', -2.000, '2026-08-05 09:00:00');
        $this->credit($client, 'overpayment', 8.125, '2026-08-06 09:00:00');
        $client->refreshBalance();

        $fresh = $client->fresh();

        $this->assertSame(34.125, (float) $fresh->credit_balance);
        $this->assertEquals(
            ['refund' => 20.250, 'manual_adjustment' => 5.750, 'overpayment' => 8.125],
            $fresh->balance_breakdown
        );
        $this->assertSame(34.125, round(array_sum($fresh->balance_breakdown), 3));
    }

    // ── Nothing zero, nothing negative, ever ────────────────────────────

    public function test_a_fully_spent_credit_type_is_omitted_rather_than_shown_as_zero(): void
    {
        $client = $this->makeClient();

        $this->credit($client, 'cashback_reward', 10.000, '2026-08-01 09:00:00');
        $this->credit($client, 'refund', 5.000, '2026-08-02 09:00:00');
        $this->credit($client, 'usage', -10.000, '2026-08-03 09:00:00'); // exactly the cashback
        $client->refreshBalance();

        $this->assertSame(['refund' => 5.0], $client->fresh()->balance_breakdown);
    }

    public function test_an_over_spent_ledger_reports_an_empty_breakdown_never_a_negative_one(): void
    {
        $client = $this->makeClient();

        $this->credit($client, 'cashback_reward', 10.000, '2026-08-01 09:00:00');
        $this->credit($client, 'usage', -25.000, '2026-08-02 09:00:00');
        $client->refreshBalance();

        $fresh = $client->fresh();

        $this->assertSame([], $fresh->balance_breakdown);
        $this->assertSame([], $fresh->auto_applicable_breakdown);
        $this->assertSame(0.0, $fresh->auto_apply_eligible_balance);
    }

    public function test_a_remainder_below_the_currency_precision_is_dropped_instead_of_shown(): void
    {
        // EUR is quoted to the cent, so a 0.004 sliver is not a displayable
        // amount and must not surface as a "€0.00" line in the breakdown.
        $client = $this->makeClient(['currency' => 'EUR']);

        $this->credit($client, 'cashback_reward', 10.004, '2026-08-01 09:00:00', 'EUR');
        $this->credit($client, 'usage', -10.000, '2026-08-02 09:00:00', 'EUR');
        $client->refreshBalance();

        $this->assertSame([], $client->fresh()->balance_breakdown);
    }

    // ── The auto-applicable subset ──────────────────────────────────────

    public function test_unchecking_a_credit_type_removes_it_from_the_auto_applicable_subset(): void
    {
        $client = $this->makeClient();

        $this->credit($client, 'cashback_reward', 10.000, '2026-08-01 09:00:00');
        $this->credit($client, 'overpayment', 20.000, '2026-08-02 09:00:00');
        $client->refreshBalance();

        // Everything is ticked by default, so the whole balance is applicable.
        $fresh = $client->fresh();
        $this->assertEquals(['cashback_reward' => 10.0, 'overpayment' => 20.0], $fresh->auto_applicable_breakdown);
        $this->assertSame(30.0, $fresh->auto_apply_eligible_balance);

        Setting::set('finix_balance.auto_apply_allowed_types', ['cashback_reward']);

        $fresh = $client->fresh();
        $this->assertEquals(['cashback_reward' => 10.0], $fresh->auto_applicable_breakdown);
        $this->assertSame(10.0, $fresh->auto_apply_eligible_balance);

        // The money is held back, not lost: the full breakdown is unchanged.
        $this->assertEquals(['cashback_reward' => 10.0, 'overpayment' => 20.0], $fresh->balance_breakdown);
        $this->assertSame(30.0, (float) $fresh->credit_balance);
    }

    public function test_an_empty_allowed_list_makes_nothing_auto_applicable(): void
    {
        Setting::set('finix_balance.auto_apply_allowed_types', []);

        $client = $this->makeClient();
        $this->credit($client, 'cashback_reward', 10.000, '2026-08-01 09:00:00');
        $client->refreshBalance();

        $fresh = $client->fresh();

        $this->assertSame([], $fresh->auto_applicable_breakdown);
        $this->assertSame(0.0, $fresh->auto_apply_eligible_balance);
        $this->assertSame(['cashback_reward' => 10.0], $fresh->balance_breakdown);
    }

    /**
     * The eligible balance charges EVERY debit against the allowed types'
     * earnings, even a debit that an older non-allowed credit really paid
     * for. That understates it — deliberately, and it must stay that way:
     * see the guard test below for the sweep it prevents.
     */
    public function test_the_eligible_balance_charges_every_debit_against_the_allowed_types(): void
    {
        Setting::set('finix_balance.auto_apply_allowed_types', ['cashback_reward']);

        $client = $this->makeClient();

        $this->credit($client, 'overpayment', 100.000, '2026-08-01 09:00:00');
        $this->credit($client, 'cashback_reward', 10.000, '2026-08-02 09:00:00');
        $this->credit($client, 'usage', -100.000, '2026-08-03 09:00:00');
        $client->refreshBalance();

        $fresh = $client->fresh();

        // The display still attributes the debit oldest-credit-first, so the
        // breakdown shows the 10 of cashback that is genuinely left...
        $this->assertSame(10.0, (float) $fresh->credit_balance);
        $this->assertSame(['cashback_reward' => 10.0], $fresh->balance_breakdown);

        // ...but the eligible figure charges all 100 of usage to the single
        // allowed type, so nothing is offered up for an automatic sweep.
        $this->assertSame(0.0, $fresh->auto_apply_eligible_balance);
    }

    /**
     * Regression guard for the reason the figure above is conservative.
     *
     * Attributing debits oldest-credit-first when computing eligibility lets
     * one call drain a forbidden credit type: each pass re-reads the ledger,
     * re-attributes the debit it just wrote to the older forbidden credit,
     * frees the allowed credit up again, and spends it once more.
     */
    public function test_a_disallowed_credit_type_can_never_be_swept_however_many_passes_run(): void
    {
        Setting::set('finix_balance.auto_apply_allowed_types', ['cashback_reward']);

        $client = $this->makeClient();

        // 100 the admin has forbidden, 50 they have allowed.
        $this->credit($client, 'refund', 100.000, '2026-08-01 09:00:00');
        $this->credit($client, 'cashback_reward', 50.000, '2026-08-02 09:00:00');
        $client->refreshBalance();

        $order = $this->makeOrder($client, 200.000, '2026-08-03');

        $this->service()->applyToUnpaidOrders($client->fresh());

        // Only the 50 of allowed cashback may ever reach the order — never
        // the 100 of refund credit, and never more than once.
        $this->assertSame(50.0, (float) $order->fresh()->paid_amount);
        $this->assertSame(100.0, (float) $client->fresh()->credit_balance);

        // Repeated calls must not chip away at the forbidden credit either.
        for ($i = 0; $i < 5; $i++) {
            $this->service()->applyToUnpaidOrders($client->fresh());
        }

        $this->assertSame(50.0, (float) $order->fresh()->paid_amount);
        $this->assertSame(100.0, (float) $client->fresh()->credit_balance);
    }

    public function test_the_eligible_balance_never_exceeds_the_total_available_balance(): void
    {
        $client = $this->makeClient();

        $this->credit($client, 'cashback_reward', 40.000, '2026-08-01 09:00:00');
        $this->credit($client, 'usage', -25.000, '2026-08-02 09:00:00');
        $client->refreshBalance();

        $fresh = $client->fresh();

        $this->assertSame(15.0, $fresh->auto_apply_eligible_balance);
        $this->assertLessThanOrEqual((float) $fresh->credit_balance, $fresh->auto_apply_eligible_balance);
    }

    // ── Millime precision ───────────────────────────────────────────────

    #[DataProvider('millimeValues')]
    public function test_a_credit_of_an_exact_millime_value_survives_the_breakdown(float $value): void
    {
        $client = $this->makeClient();

        $this->credit($client, 'cashback_reward', $value, '2026-08-01 09:00:00');
        $client->refreshBalance();

        $fresh = $client->fresh();

        $this->assertSame($value, (float) $fresh->credit_balance);
        $this->assertSame(['cashback_reward' => $value], $fresh->balance_breakdown);
        $this->assertSame($value, $fresh->auto_apply_eligible_balance);
    }

    public static function millimeValues(): array
    {
        return [
            '45.530' => [45.530],
            '0.001' => [0.001],
            '0.005' => [0.005],
            '45.555' => [45.555],
        ];
    }

    public function test_the_millime_values_split_across_types_are_neither_lost_nor_invented(): void
    {
        $client = $this->makeClient();

        $this->credit($client, 'cashback_reward', 45.530, '2026-08-01 09:00:00');
        $this->credit($client, 'overpayment', 45.555, '2026-08-02 09:00:00');
        $this->credit($client, 'refund', 0.001, '2026-08-03 09:00:00');
        $this->credit($client, 'manual_adjustment', 0.005, '2026-08-04 09:00:00');
        $client->refreshBalance();

        $fresh = $client->fresh();

        $this->assertSame(91.091, (float) $fresh->credit_balance);
        $this->assertEquals([
            'cashback_reward' => 45.530,
            'overpayment' => 45.555,
            'refund' => 0.001,
            'manual_adjustment' => 0.005,
        ], $fresh->balance_breakdown);
        $this->assertSame(91.091, $fresh->auto_apply_eligible_balance);
    }

    public function test_a_partially_consumed_credit_keeps_its_exact_millime_remainder(): void
    {
        $client = $this->makeClient();

        $this->credit($client, 'cashback_reward', 45.555, '2026-08-01 09:00:00');
        $this->credit($client, 'usage', -45.530, '2026-08-02 09:00:00');
        $client->refreshBalance();

        $fresh = $client->fresh();

        // 0.025 — not 0.03 rounded up, not 0 truncated.
        $this->assertSame(0.025, (float) $fresh->credit_balance);
        $this->assertSame(['cashback_reward' => 0.025], $fresh->balance_breakdown);
        $this->assertSame(0.025, $fresh->auto_apply_eligible_balance);
    }

    public function test_a_lone_millime_is_spent_and_attributed_like_any_other_credit(): void
    {
        $client = $this->makeClient();

        $this->credit($client, 'cashback_reward', 0.001, '2026-08-01 09:00:00');
        $this->credit($client, 'overpayment', 0.005, '2026-08-02 09:00:00');
        $this->credit($client, 'usage', -0.001, '2026-08-03 09:00:00');
        $client->refreshBalance();

        $fresh = $client->fresh();

        // The single millime of cashback is gone — 0.001 is a real amount,
        // well above the 0.0001 "close enough to zero" epsilon.
        $this->assertSame(0.005, (float) $fresh->credit_balance);
        $this->assertSame(['overpayment' => 0.005], $fresh->balance_breakdown);
    }

    // ── Idempotence / no double debit ───────────────────────────────────

    public function test_ten_consecutive_auto_apply_runs_change_nothing_after_the_first(): void
    {
        $client = $this->makeClient();
        $order = $this->makeOrder($client, 45.530, '2026-08-01');

        $this->credit($client, 'overpayment', 60.000, '2026-08-01 09:00:00');
        $client->refreshBalance();

        $this->service()->applyToUnpaidOrders($client->fresh());

        $paidAfterFirst = (float) $order->fresh()->paid_amount;
        $balanceAfterFirst = (float) $client->fresh()->credit_balance;
        $rowsAfterFirst = ClientBalanceTransaction::where('client_id', $client->id)->count();

        $this->assertSame(45.530, $paidAfterFirst);
        $this->assertSame(14.470, $balanceAfterFirst);
        $this->assertSame(2, $rowsAfterFirst); // the overpayment credit + one 'usage'

        for ($call = 2; $call <= 10; $call++) {
            $this->assertSame([], $this->service()->applyToUnpaidOrders($client->fresh()), "call {$call}");

            $this->assertSame($paidAfterFirst, (float) $order->fresh()->paid_amount, "call {$call}");
            $this->assertSame($balanceAfterFirst, (float) $client->fresh()->credit_balance, "call {$call}");
            $this->assertSame(
                $rowsAfterFirst,
                ClientBalanceTransaction::where('client_id', $client->id)->count(),
                "call {$call}"
            );
        }

        $this->assertSame(['overpayment' => 14.470], $client->fresh()->balance_breakdown);
    }

    // ── Reversion ───────────────────────────────────────────────────────

    public function test_reversing_an_automatic_application_restores_the_balance_and_its_breakdown(): void
    {
        $client = $this->makeClient();
        $order = $this->makeOrder($client, 45.530, '2026-08-01');
        $admin = $this->admin();

        $this->credit($client, 'overpayment', 45.530, '2026-08-01 09:00:00');
        $client->refreshBalance();

        $balanceBefore = (float) $client->fresh()->credit_balance;
        $this->assertSame(['overpayment' => 45.530], $client->fresh()->balance_breakdown);

        $applied = $this->service()->applyToUnpaidOrders($client->fresh());
        $txn = ClientBalanceTransaction::find($applied[0]['transaction_id']);

        $this->assertSame('completed', $order->fresh()->status);
        $this->assertSame([], $client->fresh()->balance_breakdown);

        $this->service()->reverseApplication($txn, $admin);

        $after = $client->fresh();

        $this->assertSame($balanceBefore, (float) $after->credit_balance);
        $this->assertSame(0.0, (float) $order->fresh()->paid_amount);
        $this->assertSame('pending', $order->fresh()->status);

        // The money is back in the balance. It now carries the reversal
        // row's own 'reversal' label rather than the original 'overpayment'
        // one, because the ledger is append-only — but it still sums back to
        // exactly the restored balance. 'reversal' is deliberately outside
        // the auto-appliable set, so the sweep cannot immediately undo the
        // admin's reversal by putting the money straight back on the order.
        $this->assertSame(['reversal' => 45.530], $after->balance_breakdown);
        $this->assertSame(0.0, $after->auto_apply_eligible_balance);
        $this->assertSame(round($balanceBefore, 3), round(array_sum($after->balance_breakdown), 3));

        // Nothing was deleted: the original application is still on file,
        // alongside the credit it drew on and the reversal itself.
        $this->assertNotNull(ClientBalanceTransaction::find($txn->id));
        $this->assertSame(3, ClientBalanceTransaction::where('client_id', $client->id)->count());
    }

    // ── Why the money is sitting still ──────────────────────────────────

    public function test_no_hold_reason_is_given_when_everything_available_is_on_its_way(): void
    {
        $client = $this->makeClient();
        $this->makeOrder($client, 50.000, '2026-08-01');

        $this->credit($client, 'cashback_reward', 20.000, '2026-08-01 09:00:00');
        $client->refreshBalance();

        $this->assertSame([], $client->fresh()->balance_hold_reasons);
    }

    public function test_hold_reason_feature_disabled_when_the_admin_turned_auto_apply_off(): void
    {
        Setting::set('finix_balance.auto_apply_enabled', false);

        $client = $this->makeClient();
        $this->makeOrder($client, 100.000, '2026-08-01');

        $this->credit($client, 'cashback_reward', 50.000, '2026-08-01 09:00:00');
        $client->refreshBalance();

        $fresh = $client->fresh();

        $this->assertSame(['feature_disabled'], $this->holdCodes($fresh));

        $reason = $this->holdReason($fresh, 'feature_disabled');
        $this->assertSame(50.0, $reason['amount']);
        $this->assertNull($reason['type']);
        $this->assertSame('Automatic application is currently disabled by the administrator.', $reason['message']);
    }

    public function test_hold_reason_type_not_allowed_names_the_type_and_its_exact_amount(): void
    {
        Setting::set('finix_balance.auto_apply_allowed_types', ['cashback_reward']);

        $client = $this->makeClient();
        $this->makeOrder($client, 100.000, '2026-08-01');

        $this->credit($client, 'cashback_reward', 10.000, '2026-08-01 09:00:00');
        $this->credit($client, 'overpayment', 20.500, '2026-08-02 09:00:00');
        $client->refreshBalance();

        $fresh = $client->fresh();

        $this->assertSame(['type_not_allowed'], $this->holdCodes($fresh));

        $reason = $this->holdReason($fresh, 'type_not_allowed');
        $this->assertSame('overpayment', $reason['type']);
        $this->assertSame(20.500, $reason['amount']);
        $this->assertStringContainsString('TND 20.500', $reason['message']);
        $this->assertStringContainsString(
            FinixBalanceAutoApplyService::CREDIT_TYPE_LABELS['overpayment'],
            $reason['message']
        );
    }

    public function test_hold_reason_type_not_allowed_is_emitted_once_per_excluded_type(): void
    {
        Setting::set('finix_balance.auto_apply_allowed_types', ['cashback_reward']);

        $client = $this->makeClient();
        $this->makeOrder($client, 100.000, '2026-08-01');

        $this->credit($client, 'cashback_reward', 10.000, '2026-08-01 09:00:00');
        $this->credit($client, 'refund', 5.000, '2026-08-02 09:00:00');
        $this->credit($client, 'overpayment', 7.000, '2026-08-03 09:00:00');
        $client->refreshBalance();

        $reasons = $client->fresh()->balance_hold_reasons;

        $this->assertSame(['type_not_allowed', 'type_not_allowed'], array_column($reasons, 'code'));
        $this->assertSame(
            ['refund' => 5.0, 'overpayment' => 7.0],
            array_column($reasons, 'amount', 'type')
        );
    }

    public function test_hold_reason_cashback_pending_explains_the_amount_not_yet_in_the_ledger(): void
    {
        $client = $this->makeClient();

        $this->makeOrder($client, 100.000, '2026-08-01', [
            'cashback_enabled_snapshot' => true,
            'cashback_type_snapshot' => 'fixed',
            'cashback_value_snapshot' => 10,
            'cashback_amount' => 10,
            'cashback_rewarded' => false,
        ]);

        $fresh = $client->fresh();

        $this->assertSame(['cashback_pending'], $this->holdCodes($fresh));

        $reason = $this->holdReason($fresh, 'cashback_pending');
        $this->assertSame(10.0, $reason['amount']);
        $this->assertNull($reason['type']);
        $this->assertStringContainsString('TND 10.000', $reason['message']);
        $this->assertStringContainsString('fully paid', $reason['message']);

        // It is a hold reason precisely because it is not spendable money.
        $this->assertSame([], $fresh->balance_breakdown);
        $this->assertSame(0.0, $fresh->auto_apply_eligible_balance);
    }

    public function test_hold_reason_no_unpaid_order_when_there_is_nothing_to_apply_the_balance_to(): void
    {
        $client = $this->makeClient();

        $this->credit($client, 'cashback_reward', 50.000, '2026-08-01 09:00:00');
        $client->refreshBalance();

        $fresh = $client->fresh();

        $this->assertSame(['no_unpaid_order'], $this->holdCodes($fresh));

        $reason = $this->holdReason($fresh, 'no_unpaid_order');
        $this->assertSame(50.0, $reason['amount']);
        $this->assertStringContainsString('TND 50.000', $reason['message']);
    }

    public function test_hold_reason_exceeds_amount_due_reports_only_the_leftover(): void
    {
        $client = $this->makeClient();
        $this->makeOrder($client, 20.000, '2026-08-01');

        $this->credit($client, 'cashback_reward', 50.000, '2026-08-01 09:00:00');
        $client->refreshBalance();

        $fresh = $client->fresh();

        $this->assertSame(['exceeds_amount_due'], $this->holdCodes($fresh));

        $reason = $this->holdReason($fresh, 'exceeds_amount_due');
        $this->assertSame(30.0, $reason['amount']); // 50 available − 20 due
        $this->assertStringContainsString('TND 30.000', $reason['message']);
    }

    /**
     * The two "your money is fine, it just has nowhere to go" reasons answer
     * the same question and would contradict each other — the client must
     * never be shown both at once.
     */
    public function test_no_unpaid_order_and_exceeds_amount_due_are_mutually_exclusive(): void
    {
        $scenarios = [
            'no orders at all' => fn (Client $c) => null,
            'balance well under what is due' => fn (Client $c) => $this->makeOrder($c, 500.000, '2026-08-01'),
            'balance exactly what is due' => fn (Client $c) => $this->makeOrder($c, 50.000, '2026-08-01'),
            'balance over what is due' => fn (Client $c) => $this->makeOrder($c, 20.000, '2026-08-01'),
            'only a fully paid order' => function (Client $c) {
                $order = $this->makeOrder($c, 20.000, '2026-08-01');
                \App\Models\Payment::create([
                    'client_id' => $c->id, 'order_id' => $order->id, 'amount' => 20.000,
                    'payment_method' => 'especes', 'status' => 'completed', 'payment_date' => '2026-08-02',
                    'type' => 'specific_order', 'currency' => 'TND',
                ]);
                app(\App\Services\PaymentAllocationService::class)->reallocateForClient($c->id);
            },
        ];

        foreach ($scenarios as $label => $setUp) {
            $client = $this->makeClient();
            $this->credit($client, 'cashback_reward', 50.000, '2026-08-01 09:00:00');
            $client->refreshBalance();

            $setUp($client);

            $codes = $this->holdCodes($client->fresh());

            $this->assertFalse(
                in_array('no_unpaid_order', $codes, true) && in_array('exceeds_amount_due', $codes, true),
                "both reasons emitted for scenario: {$label}"
            );
        }
    }

    public function test_hold_reasons_stack_when_several_things_are_holding_money_back(): void
    {
        Setting::set('finix_balance.auto_apply_enabled', false);

        $client = $this->makeClient();

        $this->makeOrder($client, 20.000, '2026-08-01', [
            'cashback_enabled_snapshot' => true,
            'cashback_type_snapshot' => 'fixed',
            'cashback_value_snapshot' => 4,
            'cashback_amount' => 4,
            'cashback_rewarded' => false,
        ]);

        $this->credit($client, 'cashback_reward', 50.000, '2026-08-01 09:00:00');
        $client->refreshBalance();

        // Order matters: the blocking setting first, then what is held and
        // why. 'exceeds_amount_due' is deliberately absent — it promises an
        // automatic application, which would contradict the disabled notice
        // sitting directly above it in the same list.
        $this->assertSame(
            ['feature_disabled', 'cashback_pending'],
            $this->holdCodes($client->fresh())
        );
    }

    /**
     * The second switch stops the sweep just as dead as the first, so it has
     * to produce an explanation too — otherwise a client with money, an
     * unpaid order and this toggle off is shown nothing at all.
     */
    public function test_turning_off_apply_to_old_orders_is_explained_like_any_other_block(): void
    {
        Setting::set('finix_balance.auto_apply_to_old_orders', false);

        $client = $this->makeClient();
        $this->credit($client, 'cashback_reward', 100.000, '2026-08-01 09:00:00');
        $client->refreshBalance();
        $this->makeOrder($client, 200.000, '2026-08-02');

        $this->assertSame(['feature_disabled'], $this->holdCodes($client->fresh()));
    }

    // ── The surfaces that render all of the above ───────────────────────

    public function test_the_portal_balance_card_shows_the_breakdown_the_split_and_the_hold_reasons(): void
    {
        Setting::set('finix_balance.auto_apply_allowed_types', ['cashback_reward']);

        $user = User::factory()->create(['role' => User::ROLE_CLIENT]);
        $client = $this->makeClient(['user_id' => $user->id]);

        $this->makeOrder($client, 100.000, '2026-08-01', [
            'cashback_enabled_snapshot' => true,
            'cashback_type_snapshot' => 'fixed',
            'cashback_value_snapshot' => 8,
            'cashback_amount' => 8,
            'cashback_rewarded' => false,
        ]);

        $this->credit($client, 'cashback_reward', 10.000, '2026-08-01 09:00:00');
        $this->credit($client, 'overpayment', 20.500, '2026-08-02 09:00:00');
        $client->refreshBalance();

        \Livewire\Livewire::actingAs($user)
            ->test(\App\Livewire\ClientPortal\PortalDashboard::class)
            ->assertOk()
            ->assertSee(__('Available Finix Balance'))
            // Broken down by source...
            ->assertSee(__(FinixBalanceAutoApplyService::CREDIT_TYPE_LABELS['cashback_reward']))
            ->assertSee(__(FinixBalanceAutoApplyService::CREDIT_TYPE_LABELS['overpayment']))
            ->assertSee('TND 20.500')
            // ...with the auto-applicable slice called out separately...
            ->assertSee(__('Of which applicable automatically'))
            // ...and the plain-language reason the rest is not moving.
            ->assertSee($this->holdReason($client->fresh(), 'type_not_allowed')['message'])
            ->assertSee($this->holdReason($client->fresh(), 'cashback_pending')['message']);
    }

    public function test_the_admin_client_page_shows_the_breakdown_and_the_auto_applicable_amount(): void
    {
        $client = $this->makeClient();

        $this->credit($client, 'cashback_reward', 10.000, '2026-08-01 09:00:00');
        $this->credit($client, 'refund', 20.500, '2026-08-02 09:00:00');
        $client->refreshBalance();

        \Livewire\Livewire::actingAs($this->admin())
            ->test(\App\Livewire\Clients\ClientShow::class, ['client' => $client->fresh()])
            ->assertOk()
            ->assertSee(__('Auto-applicable Now'))
            ->assertSee(__(FinixBalanceAutoApplyService::CREDIT_TYPE_LABELS['refund']))
            ->assertSee('TND 30.500');
    }

    public function test_every_hold_reason_message_is_translatable(): void
    {
        Setting::set('finix_balance.auto_apply_enabled', false);
        Setting::set('finix_balance.auto_apply_allowed_types', ['cashback_reward']);

        $client = $this->makeClient();
        $this->makeOrder($client, 5.000, '2026-08-01', [
            'cashback_enabled_snapshot' => true,
            'cashback_type_snapshot' => 'fixed',
            'cashback_value_snapshot' => 2,
            'cashback_amount' => 2,
            'cashback_rewarded' => false,
        ]);

        $this->credit($client, 'cashback_reward', 10.000, '2026-08-01 09:00:00');
        $this->credit($client, 'overpayment', 20.000, '2026-08-02 09:00:00');
        $client->refreshBalance();

        $english = $client->fresh()->balance_hold_reasons;
        $this->assertNotEmpty($english);

        app()->setLocale('fr');
        $french = $client->fresh()->balance_hold_reasons;

        $this->assertSame(
            array_column($english, 'code'),
            array_column($french, 'code')
        );

        foreach ($french as $index => $reason) {
            $this->assertNotSame(
                $english[$index]['message'],
                $reason['message'],
                "message for '{$reason['code']}' was not translated"
            );
        }
    }

    // ── Overpayment is safe to auto-apply ───────────────────────────────

    /**
     * Overpayment rows are DERIVED: reallocateForClient() deletes and
     * recreates them from payment overflow on every run. The sweep's debit
     * used to be permanent, so once overpayment became auto-appliable the
     * two could drift apart — the credit rebuilt away, the debit surviving,
     * leaving a negative balance and an order crediting more than the client
     * ever paid. Automatic applications are now rebuilt alongside it.
     */
    public function test_sweeping_an_overpayment_survives_a_later_reallocation(): void
    {
        $client = $this->makeClient();
        $order = $this->makeOrder($client, 60.000, '2026-08-01');

        // 100 paid against a 60 order leaves 40 of overpayment credit.
        \App\Models\Payment::create([
            'client_id' => $client->id, 'order_id' => $order->id, 'amount' => 100,
            'payment_method' => 'especes', 'status' => 'completed', 'payment_date' => '2026-08-02',
            'type' => 'specific_order', 'currency' => 'TND',
        ]);
        app(\App\Services\PaymentAllocationService::class)->reallocateForClient($client->id);

        $this->assertSame(40.0, (float) $client->fresh()->credit_balance);
        $this->assertSame(['overpayment' => 40.0], $client->fresh()->balance_breakdown);

        // The price rises with no reallocation (OrderForm only reallocates
        // for brand new orders), so the payment allocation still covers just
        // the original 60. The sweep then puts the 40 of overpayment on top.
        $order->update(['price' => 150.000]);
        $this->service()->applyToUnpaidOrders($client->fresh());

        $this->assertSame(100.0, (float) $order->fresh()->paid_amount);
        $this->assertSame(0.0, (float) $client->fresh()->credit_balance);

        // Any later reallocation must rebuild BOTH sides together.
        app(\App\Services\PaymentAllocationService::class)->reallocateForClient($client->id);

        $after = $client->fresh();

        // 100 of real money paid, so the order may never show more than 100
        // and the balance may never go negative.
        $this->assertSame(100.0, (float) $order->fresh()->paid_amount);
        $this->assertSame(0.0, (float) $after->credit_balance);
        $this->assertSame([], $after->balance_breakdown);
    }

    public function test_repeated_reallocations_never_drift_the_balance_or_the_order(): void
    {
        $client = $this->makeClient();
        $paidOrder = $this->makeOrder($client, 60.000, '2026-08-01');
        $unpaidOrder = $this->makeOrder($client, 25.000, '2026-08-05');

        \App\Models\Payment::create([
            'client_id' => $client->id, 'order_id' => $paidOrder->id, 'amount' => 100,
            'payment_method' => 'especes', 'status' => 'completed', 'payment_date' => '2026-08-02',
            'type' => 'specific_order', 'currency' => 'TND',
        ]);

        $allocator = app(\App\Services\PaymentAllocationService::class);
        $allocator->reallocateForClient($client->id);

        $balance = (float) $client->fresh()->credit_balance;
        $paid = (float) $unpaidOrder->fresh()->paid_amount;

        for ($i = 0; $i < 5; $i++) {
            $allocator->reallocateForClient($client->id);

            $this->assertSame($balance, (float) $client->fresh()->credit_balance, "balance drifted on run {$i}");
            $this->assertSame($paid, (float) $unpaidOrder->fresh()->paid_amount, "allocation drifted on run {$i}");
        }
    }

    /**
     * Sweeping overpayment writes a 'usage' row, which the old
     * min(earned, every debit) formula counted as cashback being spent.
     */
    public function test_spending_overpayment_does_not_consume_the_cashback_figures(): void
    {
        $client = $this->makeClient();

        $this->credit($client, 'cashback_reward', 50.000, '2026-08-01 09:00:00');
        $this->credit($client, 'overpayment', 100.000, '2026-08-02 09:00:00');
        $client->refreshBalance();

        $order = $this->makeOrder($client, 100.000, '2026-08-03');
        $this->service()->applyToUnpaidOrders($client->fresh());

        $after = $client->fresh();

        // The 100 swept came out of the cashback first (it is older), so 50
        // of overpayment is what survives — and no cashback remains.
        $this->assertSame(50.0, (float) $after->credit_balance);
        $this->assertSame(['overpayment' => 50.0], $after->balance_breakdown);
        $this->assertSame(0.0, $after->cashback_available);

        // Now the reverse ordering: overpayment is older, so it absorbs the
        // sweep and every dinar of cashback is still the client's.
        $other = $this->makeClient(['name' => 'Ines']);
        $this->credit($other, 'overpayment', 100.000, '2026-08-01 09:00:00');
        $this->credit($other, 'cashback_reward', 50.000, '2026-08-02 09:00:00');
        $other->refreshBalance();

        $this->makeOrder($other, 100.000, '2026-08-03');
        $this->service()->applyToUnpaidOrders($other->fresh());

        $afterOther = $other->fresh();

        $this->assertSame(50.0, (float) $afterOther->credit_balance);
        $this->assertSame(['cashback_reward' => 50.0], $afterOther->balance_breakdown);
        $this->assertSame(50.0, $afterOther->cashback_available);
        $this->assertSame(0.0, $afterOther->cashback_used);
    }

    /**
     * floor() on a float product undershoots for values like 1.15 (1.15*100
     * is 114.999…), which used to split one application into two ledger rows.
     */
    public function test_an_application_is_never_split_into_two_rows_by_float_error(): void
    {
        $client = $this->makeClient(['currency' => 'EUR']);

        $this->credit($client, 'cashback_reward', 5.000, '2026-08-01 09:00:00', 'EUR');
        $client->refreshBalance();

        $order = $this->makeOrder($client, 1.15, '2026-08-02', ['currency' => 'EUR']);

        $applied = $this->service()->applyToUnpaidOrders($client->fresh());

        $this->assertCount(1, $applied);
        $this->assertSame(1.15, $applied[0]['amount']);
        $this->assertSame('completed', $order->fresh()->status);
    }
}
