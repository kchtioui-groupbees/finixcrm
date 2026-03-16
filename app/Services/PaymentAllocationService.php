<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Order;
use App\Models\Payment;
use App\Services\CashbackRewardService;
use Illuminate\Support\Facades\DB;

/**
 * PaymentAllocationService  (v3 — full reconciliation)
 *
 * This is the SINGLE source of truth for all financial allocations.
 *
 * Every time it runs for a client, it:
 *
 * Phase A — PAYMENT PASS
 *   1. Lock the client row (concurrency safety).
 *   2. Wipe ALL payment_allocations for this client's orders.
 *   3. Wipe ALL auto-generated balance transactions (overpayment + usage).
 *      Note: cashback_reward and manual_adjustment transactions are NEVER touched.
 *   4. Rebuild payment-based allocations from oldest payment → oldest order.
 *   5. Any leftover payment funds → 'overpayment' balance transaction.
 *
 * Phase B — CREDIT PASS
 *   6. After payments are distributed, calculate the net available credit
 *      (sum of cashback_reward + overpayment transactions — no usage yet).
 *   7. Walk still-pending orders and apply available credit to cover them.
 *      Each credit application creates:
 *        - a 'usage' balance transaction  (negative, permanent audit trail)
 *        - a payment_allocation linked to that transaction
 *   8. Update order statuses.
 *   9. Refresh client.credit_balance (the ledger sum).
 *
 * Phase C — CASHBACK PASS  (AFTER main transaction commits)
 *  10. Trigger CashbackRewardService for newly-completed orders.
 *  11. Re-sync credit_balance.
 *
 * RESULT:
 *   - credit_balance = (cashback rewards) + (overpayments) — (credit used)
 *   - pending_amount on each order = 0 if covered by payments + credit
 *   - dashboard shows a consistent picture with zero phantom debits
 */
class PaymentAllocationService
{
    public function reallocateForClient(int $clientId): void
    {
        $newlyCompleted = [];

        DB::transaction(function () use ($clientId, &$newlyCompleted) {

            // ── STEP 1: Lock ──────────────────────────────────────────────
            $client = Client::lockForUpdate()->find($clientId);
            if (!$client) return;

            $orderIds = Order::where('client_id', $clientId)->pluck('id');
            if ($orderIds->isEmpty()) return;

            // ── STEP 2: Wipe ALL allocations (payment + credit) ───────────
            DB::table('payment_allocations')
                ->whereIn('order_id', $orderIds)
                ->delete();

            // ── STEP 3: Wipe auto-generated balance transactions ──────────
            // Remove 'overpayment' (excess payments) and 'usage' (credit spend).
            // We intentionally keep: cashback_reward, manual_adjustment, refund, etc.
            DB::table('client_balance_transactions')
                ->where('client_id', $clientId)
                ->whereIn('type', ['overpayment', 'usage'])
                ->delete();

            // ── STEP 4: Build payment buckets (oldest first) ──────────────
            $payments = Payment::where('client_id', $clientId)
                ->where('status', 'completed')
                ->orderBy('payment_date')
                ->orderBy('id')
                ->get();

            $buckets = [];
            foreach ($payments as $p) {
                $buckets[$p->id] = ['remaining' => (float) $p->amount, 'currency' => $p->currency];
            }

            // ── STEP 5: Payment pass — oldest order first ─────────────────
            $orders = Order::where('client_id', $clientId)
                ->orderBy('purchase_date')
                ->orderBy('id')
                ->get();

            // Track per-order how much was covered by payments (for the credit pass below)
            $paymentCoverage = []; // order_id => float

            foreach ($orders as $order) {
                $needed   = (float) $order->price;
                $covered  = 0.0;
                $inserts  = [];

                foreach ($buckets as $paymentId => &$bucket) {
                    if ($bucket['remaining'] <= 0.001 || $covered >= $needed - 0.001) break;

                    $take           = min($bucket['remaining'], $needed - $covered);
                    $bucket['remaining'] -= $take;
                    $covered        += $take;

                    $inserts[] = [
                        'payment_id'             => $paymentId,
                        'balance_transaction_id' => null,
                        'order_id'               => $order->id,
                        'amount'                 => round($take, 4),
                        'created_at'             => now(),
                        'updated_at'             => now(),
                    ];
                }
                unset($bucket);

                if (!empty($inserts)) {
                    DB::table('payment_allocations')->insert($inserts);
                }

                $paymentCoverage[$order->id] = round($covered, 4);
            }

            // ── STEP 5b: Overpayment credits ─────────────────────────────
            $currency = $client->currency ?? 'TND';
            foreach ($buckets as $paymentId => $bucket) {
                if ($bucket['remaining'] > 0.001) {
                    DB::table('client_balance_transactions')->insert([
                        'client_id'      => $clientId,
                        'amount'         => round($bucket['remaining'], 4),
                        'type'           => 'overpayment',
                        'payment_id'     => $paymentId,
                        'description'    => "Overpayment from payment #{$paymentId}",
                        'currency'       => $bucket['currency'],
                        'reference_type' => null,
                        'reference_id'   => null,
                        'created_at'     => now(),
                        'updated_at'     => now(),
                    ]);
                }
            }

            // ── STEP 6: Calculate available credit (pre-usage) ────────────
            // Credit = cashback_reward + overpayment (usage was wiped, so it's 0 now)
            $availableCredit = (float) DB::table('client_balance_transactions')
                ->where('client_id', $clientId)
                ->whereIn('type', ['cashback_reward', 'overpayment', 'manual_adjustment', 'refund'])
                ->sum('amount');

            $availableCredit = max(0.0, round($availableCredit, 4));

            // ── STEP 7: Credit pass — apply credit to still-pending orders ─
            foreach ($orders as $order) {
                if ($availableCredit <= 0.001) break;

                $price        = (float) $order->price;
                $paidByMoney  = $paymentCoverage[$order->id] ?? 0.0;
                $stillPending = round($price - $paidByMoney, 4);

                if ($stillPending <= 0.001) continue; // already fully covered by payments

                $applyCredit = min($availableCredit, $stillPending);

                // Create a 'usage' ledger entry (negative = debit from wallet)
                $txnId = DB::table('client_balance_transactions')->insertGetId([
                    'client_id'      => $clientId,
                    'amount'         => -round($applyCredit, 4),
                    'type'           => 'usage',
                    'payment_id'     => null,
                    'description'    => "Credit applied to Order #{$order->id}",
                    'currency'       => $currency,
                    'reference_type' => 'order',
                    'reference_id'   => $order->id,
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ]);

                // Create allocation linked to that usage transaction
                DB::table('payment_allocations')->insert([
                    'payment_id'             => null,
                    'balance_transaction_id' => $txnId,
                    'order_id'               => $order->id,
                    'amount'                 => round($applyCredit, 4),
                    'created_at'             => now(),
                    'updated_at'             => now(),
                ]);

                $availableCredit -= $applyCredit;
            }

            // ── STEP 8: Update order statuses ─────────────────────────────
            // Re-load orders so paid_amount accessor reflects the new allocations.
            $freshOrders = Order::where('client_id', $clientId)
                ->orderBy('purchase_date')
                ->orderBy('id')
                ->get();

            foreach ($freshOrders as $order) {
                $wasCompleted = ($order->status === 'completed');
                $paidSoFar    = round($order->paid_amount, 4);
                $orderPrice   = (float) $order->price;

                if ($paidSoFar <= 0.001) {
                    DB::table('orders')->where('id', $order->id)->update(['status' => 'pending']);
                } elseif ($paidSoFar < $orderPrice - 0.001) {
                    DB::table('orders')->where('id', $order->id)->update(['status' => 'partially_paid']);
                } else {
                    DB::table('orders')->where('id', $order->id)->update(['status' => 'completed']);
                    if (!$wasCompleted || !$order->cashback_rewarded) {
                        $newlyCompleted[] = $order->id;
                    }
                }
            }

            // ── STEP 9: Refresh cached balance ────────────────────────────
            $newBalance = DB::table('client_balance_transactions')
                ->where('client_id', $clientId)
                ->sum('amount');
            DB::table('clients')
                ->where('id', $clientId)
                ->update(['credit_balance' => round((float) $newBalance, 4)]);

        }); // end DB::transaction

        // ── STEP 10: Cashback (outside transaction) ────────────────────────
        $cashbackSvc = app(CashbackRewardService::class);
        foreach ($newlyCompleted as $orderId) {
            $order = Order::find($orderId);
            if ($order) {
                $cashbackSvc->rewardIfEligible($order);
            }
        }

        // ── STEP 11: Re-sync balance after cashback credits ───────────────
        if (!empty($newlyCompleted)) {
            $newBalance = DB::table('client_balance_transactions')
                ->where('client_id', $clientId)
                ->sum('amount');
            DB::table('clients')
                ->where('id', $clientId)
                ->update(['credit_balance' => round((float) $newBalance, 4)]);
        }
    }
}
