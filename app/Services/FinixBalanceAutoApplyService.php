<?php

namespace App\Services;

use App\Models\Client;
use App\Models\ClientBalanceTransaction;
use App\Models\Order;
use App\Models\PaymentAllocation;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * FinixBalanceAutoApplyService
 *
 * Automatically applies a client's available Finix balance to their
 * unpaid orders, oldest first — controlled by admin settings, and always
 * kept separate from "pending" cashback (which has no ledger entry yet,
 * so it can never be drawn from here).
 *
 * Guarantees:
 *  - No-op unless auto-apply is enabled AND "apply to old unpaid orders"
 *    is enabled (both admin settings, default true).
 *  - Only draws from the configured allowed credit types. The default is
 *    every credit source the ledger can hold: cashback_reward, refund,
 *    manual_adjustment and overpayment (CREDIT_TYPE_LABELS below is that
 *    same set); the settings screen offers exactly those four, so an admin
 *    can only narrow the list, never widen it.
 *  - Pending cashback can never be drawn from here, and that is
 *    structural rather than a setting: pending cashback lives only on the
 *    order (cashback_enabled_snapshot true, cashback_rewarded false) and
 *    has no ClientBalanceTransaction row at all until the order is fully
 *    paid, so there is nothing in the ledger for this service to spend.
 *  - Locks the client row for the duration, so concurrent calls can never
 *    apply the same credit twice.
 *  - Oldest unpaid order first (by purchase_date, then id).
 *  - Partial application when balance < remaining; capped at the
 *    remaining amount when balance > remaining. Leftover balance stays
 *    available for the next order or a future application.
 *  - Every application is one 'usage' ledger transaction (created_by
 *    null — the signal that distinguishes an automatic application from
 *    an admin's explicit applyCredit()) plus one payment_allocation
 *    linking it to the order — the same durable, additive audit trail
 *    every other credit movement in this app uses.
 *  - Fully paying off an order via balance can itself release that
 *    order's own pending cashback (CashbackRewardService), which can in
 *    turn fund further applications — handled as a bounded loop so that
 *    cascade settles in one call.
 *  - Never touches Payment or revenue figures: this never creates a
 *    Payment row, so it structurally cannot be counted as bank/cash
 *    revenue anywhere in the app.
 */
class FinixBalanceAutoApplyService
{
    /**
     * The credit types auto-apply may ever be configured to draw from — the
     * settings screen offers exactly these as checkboxes, and they are the
     * default. Deliberately NOT every type that can appear in the ledger:
     * REVERSAL_TYPE is excluded so an admin cannot re-enable the loop where
     * reversing an application immediately re-applies it.
     */
    public const AUTO_APPLIABLE_TYPES = [
        'cashback_reward',
        'refund',
        'manual_adjustment',
        'overpayment',
    ];

    /**
     * Display labels for every credit type that can appear in the ledger —
     * a superset of AUTO_APPLIABLE_TYPES, since the breakdown has to be able
     * to name reversal credit too. English source strings; each surface
     * wraps them in __().
     */
    public const CREDIT_TYPE_LABELS = [
        'cashback_reward'   => 'Cashback',
        'refund'            => 'Refund / Credit note',
        'manual_adjustment' => 'Manual adjustment',
        'overpayment'       => 'Overpayment',
        self::REVERSAL_TYPE => 'Reversed application',
    ];

    private const MAX_PASSES = 25; // safety bound on the cascade loop, not a real limit in practice

    /**
     * Ledger type used for the credit written back by reverseApplication().
     * Kept out of the auto-apply allowed list on purpose — see the comment
     * at the point of creation.
     */
    public const REVERSAL_TYPE = 'reversal';

    /**
     * Has this automatic application already been reversed? A reversal is
     * recorded as a credit pointing back at the original transaction, so
     * that link is the check — nothing is ever deleted or flagged in place.
     */
    public static function isReversed(ClientBalanceTransaction $transaction): bool
    {
        return ClientBalanceTransaction::where('reference_type', 'reversal')
            ->where('reference_id', $transaction->id)
            ->exists();
    }

    public function isEnabled(): bool
    {
        return (bool) Setting::get('finix_balance.auto_apply_enabled', true);
    }

    public function appliesToOldUnpaidOrders(): bool
    {
        return (bool) Setting::get('finix_balance.auto_apply_to_old_orders', true);
    }

    public function allowedTypes(): array
    {
        $types = Setting::get('finix_balance.auto_apply_allowed_types', self::AUTO_APPLIABLE_TYPES);

        if (!is_array($types)) {
            return [];
        }

        // A stored setting can only ever narrow the list, never widen it past
        // what the screen offers — so a legacy or hand-edited row can't turn
        // reversal credit into something the sweep will spend.
        return array_values(array_intersect($types, self::AUTO_APPLIABLE_TYPES));
    }

    /**
     * Apply the client's eligible available balance to their unpaid
     * orders, oldest first, cascading through any cashback that gets
     * released as a result. Returns the list of applications made:
     * [['order_id' => int, 'amount' => float, 'transaction_id' => int], ...]
     */
    public function applyToUnpaidOrders(Client $client): array
    {
        if (!$this->isEnabled() || !$this->appliesToOldUnpaidOrders()) {
            return [];
        }

        $applied = [];
        $newlyCompletedOrderIds = [];

        for ($pass = 0; $pass < self::MAX_PASSES; $pass++) {
            $passApplied = [];

            DB::transaction(function () use ($client, &$passApplied, &$newlyCompletedOrderIds) {
                $locked = Client::where('id', $client->id)->lockForUpdate()->first();
                if (!$locked) {
                    return;
                }

                // Rounded to the max precision the ledger supports (3); the
                // amount actually taken for each order is re-rounded to
                // that specific order's currency precision below.
                $available = round($locked->auto_apply_eligible_balance, 3);
                if ($available <= 0.0001) {
                    return;
                }

                $orders = Order::where('client_id', $locked->id)
                    ->orderBy('purchase_date')
                    ->orderBy('id')
                    ->get()
                    ->filter(fn ($o) => $o->pending_amount > 0.0001);

                foreach ($orders as $order) {
                    if ($available <= 0.0001) {
                        break;
                    }

                    $orderPrecision = $this->precision($order->currency);
                    // Floor, never round: on a 2-decimal order funded from a
                    // 3-decimal TND balance, rounding 5.005 half-up to 5.01
                    // would spend a millime the client does not have (and
                    // over-pay the order by the same amount).
                    // The inner round() absorbs float representation error
                    // before flooring — without it 1.15 * 100 is 114.999…,
                    // which floors to 1.14 and splits one application into
                    // two ledger rows.
                    $factor = 10 ** $orderPrecision;
                    $toApply = floor(round(min($available, $order->pending_amount) * $factor, 6)) / $factor;
                    if ($toApply <= 0) {
                        continue;
                    }

                    $txn = ClientBalanceTransaction::create([
                        'client_id' => $locked->id,
                        'amount' => -$toApply,
                        'type' => 'usage',
                        'description' => sprintf(
                            '%s de solde Finix appliqués automatiquement à la commande #%d.',
                            number_format($toApply, $orderPrecision) . ' ' . $order->currency,
                            $order->id
                        ),
                        'currency' => $order->currency,
                        'reference_type' => 'order',
                        'reference_id' => $order->id,
                        'created_by' => null, // null = automatic application, never an admin's manual action
                    ]);

                    PaymentAllocation::create([
                        'balance_transaction_id' => $txn->id,
                        'order_id' => $order->id,
                        'amount' => $toApply,
                    ]);

                    $available -= $toApply;
                    $passApplied[] = ['order_id' => $order->id, 'amount' => $toApply, 'transaction_id' => $txn->id];

                    $order->refresh();
                    $paid = round($order->paid_amount, $orderPrecision);
                    $price = (float) $order->price;

                    if ($paid >= $price - 0.0001) {
                        $order->update(['status' => 'completed']);
                        $newlyCompletedOrderIds[] = $order->id;
                    } elseif ($paid > 0.0001) {
                        $order->update(['status' => 'partially_paid']);
                    }
                }

                $locked->refreshBalance();
            });

            $applied = array_merge($applied, $passApplied);

            if (empty($passApplied)) {
                break; // nothing left to apply this pass — done
            }
        }

        // Release cashback for any order this just fully paid off, which
        // may fund another pass above on the next call — but to settle a
        // same-request cascade (balance -> pays order -> releases its
        // cashback -> pays another order) we loop once more here.
        if (!empty($newlyCompletedOrderIds)) {
            $cashbackSvc = app(CashbackRewardService::class);
            $anyRewarded = false;

            foreach (array_unique($newlyCompletedOrderIds) as $orderId) {
                $order = Order::find($orderId);
                if ($order && $cashbackSvc->rewardIfEligible($order)) {
                    $anyRewarded = true;
                }
            }

            if ($anyRewarded) {
                $applied = array_merge($applied, $this->applyToUnpaidOrders($client->fresh()));
            }
        }

        return $applied;
    }

    /**
     * Reverse an automatic balance application — the ONLY way one can be
     * undone, and only ever by an explicit, controlled admin action.
     * Never deletes the original transaction or allocation (full audit
     * trail preserved); credits the balance back and offsets the
     * allocation additively.
     */
    public function reverseApplication(ClientBalanceTransaction $transaction, User $admin): void
    {
        if ($transaction->type !== 'usage' || $transaction->created_by !== null || $transaction->reference_type !== 'order') {
            throw new RuntimeException('This transaction is not a reversible automatic balance application.');
        }

        // Without this, a second click on the Reverse button (the row keeps
        // it, and a double-submit is enough) mints another full-amount credit
        // out of nothing — the reversal is additive, so nothing else stops it.
        if (self::isReversed($transaction)) {
            throw new RuntimeException('This automatic balance application has already been reversed.');
        }

        DB::transaction(function () use ($transaction, $admin) {
            $amount = round(abs((float) $transaction->amount), $this->precision($transaction->currency));
            $orderId = $transaction->reference_id;

            $reversal = ClientBalanceTransaction::create([
                'client_id' => $transaction->client_id,
                'amount' => $amount,
                // Deliberately NOT 'manual_adjustment': that type is in the
                // default allowed list, so the very next sweep would put the
                // money straight back on the order the admin just took it off
                // and the reversal would silently undo itself. 'reversal'
                // credit is real, spendable balance — it simply has to be
                // applied deliberately rather than automatically.
                'type' => self::REVERSAL_TYPE,
                'description' => "Reversal of automatic Finix balance application (transaction #{$transaction->id}) on order #{$orderId}",
                'currency' => $transaction->currency,
                'reference_type' => 'reversal',
                'reference_id' => $transaction->id,
                'created_by' => $admin->id,
            ]);

            // Offset the original allocation additively — never delete it.
            PaymentAllocation::create([
                'balance_transaction_id' => $reversal->id,
                'order_id' => $orderId,
                'amount' => -$amount,
            ]);

            if ($order = Order::find($orderId)) {
                $order->refresh();
                $paid = round($order->paid_amount, $this->precision($order->currency));
                $price = (float) $order->price;

                if ($paid <= 0.0001) {
                    $order->update(['status' => 'pending']);
                } elseif ($paid < $price - 0.0001) {
                    $order->update(['status' => 'partially_paid']);
                }
            }

            if ($client = Client::find($transaction->client_id)) {
                $client->refreshBalance();
            }
        });
    }

    // ── Helpers ─────────────────────────────────────────────────────────────

    /** TND is quoted to the millime (3 decimals); every other currency here uses 2. */
    private function precision(?string $currency): int
    {
        return $currency === 'TND' ? 3 : 2;
    }
}
