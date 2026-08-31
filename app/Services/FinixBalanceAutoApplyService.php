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
 *  - Only draws from the configured allowed credit types (default:
 *    cashback_reward, refund, manual_adjustment — never overpayment
 *    unless explicitly allowed, and structurally never pending cashback).
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
    private const MAX_PASSES = 25; // safety bound on the cascade loop, not a real limit in practice

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
        $types = Setting::get('finix_balance.auto_apply_allowed_types', [
            'cashback_reward', 'refund', 'manual_adjustment',
        ]);

        return is_array($types) ? $types : [];
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
                    $toApply = round(min($available, $order->pending_amount), $orderPrecision);
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

        DB::transaction(function () use ($transaction, $admin) {
            $amount = round(abs((float) $transaction->amount), $this->precision($transaction->currency));
            $orderId = $transaction->reference_id;

            $reversal = ClientBalanceTransaction::create([
                'client_id' => $transaction->client_id,
                'amount' => $amount,
                'type' => 'manual_adjustment',
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
