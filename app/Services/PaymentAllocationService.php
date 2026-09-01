<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Order;
use App\Models\Payment;
use App\Services\CashbackRewardService;
use App\Services\FinixBalanceAutoApplyService;
use Illuminate\Support\Facades\DB;

/**
 * PaymentAllocationService  (v5 — payments, then cashback, then auto-apply)
 *
 * This is the SINGLE source of truth for reconciling a client's payments,
 * cashback rewards, and (per an admin setting) their available Finix
 * balance against their orders.
 *
 * Every time it runs for a client, it:
 *
 * Phase A — PAYMENT PASS
 *   1. Lock the client row (concurrency safety).
 *   2. Wipe only the PAYMENT-based payment_allocations for this client's
 *      orders (rows with a payment_id). Credit-based allocations (linked to
 *      a balance_transaction_id — i.e. an admin's explicit applyCredit(),
 *      or an automatic application from FinixBalanceAutoApplyService)
 *      are never touched, so applied credit survives every future
 *      reallocation instead of being silently wiped and never rebuilt.
 *   3. Wipe only the 'overpayment' balance transactions (purely derived
 *      from payment overflow, safe to recompute every time).
 *      Note: cashback_reward, manual_adjustment, refund, and usage
 *      transactions are NEVER touched — usage entries are the durable
 *      record of an applied credit (manual or automatic).
 *   4. Rebuild payment-based allocations from oldest payment → oldest
 *      order, first subtracting whatever credit-based amount already
 *      covers that order so payments never double-cover it.
 *   5. Any leftover payment funds → 'overpayment' balance transaction.
 *   6. Update order statuses from the resulting paid_amount (payments +
 *      any credit already applied).
 *   7. Refresh client.credit_balance (the ledger sum).
 *
 * Phase B — CASHBACK PASS  (AFTER main transaction commits)
 *   8. Trigger CashbackRewardService for newly-completed orders.
 *   9. Re-sync credit_balance.
 *
 * Phase C — AUTO-APPLY PASS  (admin-configurable, default on)
 *   10. FinixBalanceAutoApplyService applies whatever balance is now
 *       available (from the admin-configured allowed credit types —
 *       never pending cashback, which has no ledger entry yet) to the
 *       client's oldest unpaid orders. Fully documented, reversible only
 *       by a controlled admin action — see that service.
 *
 * RESULT:
 *   - credit_balance = (cashback rewards) + (overpayments) + (manual
 *     adjustments) + (refunds) — (credit applied, manually or
 *     automatically)
 *   - pending_amount on each order = 0 once actual payments and/or
 *     applied credit cover it
 *   - dashboard shows a consistent picture with zero phantom debits, and
 *     applied balance never counts as bank/cash revenue (it never creates
 *     a Payment row)
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

            // ── STEP 2: Wipe only PAYMENT-based allocations ────────────────
            // Credit-based allocations (balance_transaction_id set — i.e. an
            // admin's explicit applyCredit()) are left alone so they survive
            // this rebuild instead of being silently discarded.
            DB::table('payment_allocations')
                ->whereIn('order_id', $orderIds)
                ->whereNotNull('payment_id')
                ->delete();

            // ── STEP 3: Wipe only auto-generated 'overpayment' transactions ─
            // These are purely derived from payment overflow and safe to
            // recompute every time. cashback_reward, manual_adjustment,
            // refund and an admin's explicit applyCredit() 'usage' are never
            // touched here.
            DB::table('client_balance_transactions')
                ->where('client_id', $clientId)
                ->where('type', 'overpayment')
                ->delete();

            // ── STEP 3a: Wipe automatic balance applications too ───────────
            // These are just as derived as the overpayment rows above: STEP
            // 10 recomputes them from the settled balance. Wiping both
            // together is what keeps them consistent.
            //
            // Leaving them behind is not an option now that 'overpayment' is
            // auto-appliable by default. The sweep's debit used to be
            // permanent while the credit funding it was rebuilt on every
            // run, so the two could drift apart: a client who overpays 40,
            // has it swept onto an order, and then triggers any reallocation
            // ends up with the +40 credit deleted, the -40 debit surviving, a
            // negative balance, and an order crediting 140 against 100 of
            // real money.
            //
            // Only fully automatic, un-reversed applications qualify:
            //  - created_by null distinguishes the sweep from an admin's own
            //    applyCredit(), which stays sticky as before;
            //  - a reversed one is left alone with its reversal credit, so an
            //    admin's deliberate undo is never quietly re-applied (the
            //    pair nets to zero, so it cannot skew the rebuild either).
            $reversedIds = DB::table('client_balance_transactions')
                ->where('client_id', $clientId)
                ->where('reference_type', 'reversal')
                ->pluck('reference_id');

            $staleAutoApplications = DB::table('client_balance_transactions')
                ->where('client_id', $clientId)
                ->where('type', 'usage')
                ->whereNull('created_by')
                ->where('reference_type', 'order')
                ->whereNotIn('id', $reversedIds)
                ->pluck('id');

            if ($staleAutoApplications->isNotEmpty()) {
                DB::table('payment_allocations')
                    ->whereIn('balance_transaction_id', $staleAutoApplications)
                    ->delete();

                DB::table('client_balance_transactions')
                    ->whereIn('id', $staleAutoApplications)
                    ->delete();
            }

            // ── STEP 3b: How much of each order is already covered by an
            // existing, explicitly-applied credit allocation? Payments must
            // never double-cover that portion.
            $existingCreditCoverage = DB::table('payment_allocations')
                ->whereIn('order_id', $orderIds)
                ->whereNotNull('balance_transaction_id')
                ->selectRaw('order_id, SUM(amount) as total')
                ->groupBy('order_id')
                ->pluck('total', 'order_id');

            // ── STEP 4: Build payment buckets (oldest first) ──────────────
            // Renewal payments are recorded and reconciled by RenewalService /
            // the renewal flow directly — they must never enter the FIFO
            // reallocation below, or they'd be misread as an overpayment /
            // client credit instead of a plain recurring charge.
            $payments = Payment::where('client_id', $clientId)
                ->where('status', 'completed')
                ->where('type', '!=', 'renewal')
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

            foreach ($orders as $order) {
                $creditCovered = (float) ($existingCreditCoverage[$order->id] ?? 0);
                $needed   = max(0.0, (float) $order->price - $creditCovered);
                $covered  = 0.0;
                $inserts  = [];

                foreach ($buckets as $paymentId => &$bucket) {
                    if ($bucket['remaining'] <= 0.0001 || $covered >= $needed - 0.0001) break;

                    $take           = min($bucket['remaining'], $needed - $covered);
                    $bucket['remaining'] -= $take;
                    $covered        += $take;

                    $inserts[] = [
                        'payment_id'             => $paymentId,
                        'balance_transaction_id' => null,
                        'order_id'               => $order->id,
                        'amount'                 => round($take, $this->precision($order->currency)),
                        'created_at'             => now(),
                        'updated_at'             => now(),
                    ];
                }
                unset($bucket);

                if (!empty($inserts)) {
                    DB::table('payment_allocations')->insert($inserts);
                }
            }

            // ── STEP 5b: Overpayment credits ─────────────────────────────
            $currency = $client->currency ?? 'TND';
            foreach ($buckets as $paymentId => $bucket) {
                if ($bucket['remaining'] > 0.0001) {
                    DB::table('client_balance_transactions')->insert([
                        'client_id'      => $clientId,
                        'amount'         => round($bucket['remaining'], $this->precision($bucket['currency'])),
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

            // Credit (cashback rewards, overpayments, manual adjustments,
            // refunds) is intentionally NEVER auto-applied to a pending
            // order here. It only reduces what a client owes when an admin
            // explicitly applies it via ClientTransactions::applyCredit(),
            // whose 'usage' ledger entries and allocations were preserved
            // above (Step 2/3 only touch payment-based rows and
            // 'overpayment' transactions).

            // ── STEP 6: Update order statuses ──────────────────────────────
            // Re-load orders so paid_amount accessor reflects the new allocations.
            $freshOrders = Order::where('client_id', $clientId)
                ->orderBy('purchase_date')
                ->orderBy('id')
                ->get();

            foreach ($freshOrders as $order) {
                $wasCompleted = ($order->status === 'completed');
                $paidSoFar    = round($order->paid_amount, $this->precision($order->currency));
                $orderPrice   = (float) $order->price;

                if ($paidSoFar <= 0.0001) {
                    DB::table('orders')->where('id', $order->id)->update(['status' => 'pending']);
                } elseif ($paidSoFar < $orderPrice - 0.0001) {
                    DB::table('orders')->where('id', $order->id)->update(['status' => 'partially_paid']);
                } else {
                    DB::table('orders')->where('id', $order->id)->update(['status' => 'completed']);
                    if (!$wasCompleted || !$order->cashback_rewarded) {
                        $newlyCompleted[] = $order->id;
                    }
                }
            }

            // ── STEP 7: Refresh cached balance ─────────────────────────────
            $newBalance = DB::table('client_balance_transactions')
                ->where('client_id', $clientId)
                ->sum('amount');
            DB::table('clients')
                ->where('id', $clientId)
                ->update(['credit_balance' => round((float) $newBalance, $this->precision($client->currency ?? null))]);

        }); // end DB::transaction

        // $client was scoped to the transaction closure above and isn't
        // visible here — re-fetch it once for the remaining, outside-the-
        // transaction steps.
        $client = Client::find($clientId);
        if (!$client) {
            return;
        }

        // ── STEP 8: Cashback (outside transaction) ─────────────────────────
        $cashbackSvc = app(CashbackRewardService::class);
        foreach ($newlyCompleted as $orderId) {
            $order = Order::find($orderId);
            if ($order) {
                $cashbackSvc->rewardIfEligible($order);
            }
        }

        // ── STEP 9: Re-sync balance after cashback credits ─────────────────
        if (!empty($newlyCompleted)) {
            $newBalance = DB::table('client_balance_transactions')
                ->where('client_id', $clientId)
                ->sum('amount');
            DB::table('clients')
                ->where('id', $clientId)
                ->update(['credit_balance' => round((float) $newBalance, $this->precision($client->currency))]);
        }

        // ── STEP 10: Auto-apply available balance to unpaid orders ─────────
        // Config-gated (admin setting, default on) — see
        // FinixBalanceAutoApplyService for the full rules. Runs after
        // payments and cashback have settled so it sees the final picture.
        app(FinixBalanceAutoApplyService::class)->applyToUnpaidOrders($client->fresh());
    }

    // ── Helpers ─────────────────────────────────────────────────────────────

    private function precision(?string $currency): int
    {
        return $currency === 'TND' ? 3 : 2;
    }
}
