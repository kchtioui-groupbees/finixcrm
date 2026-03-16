<?php

namespace App\Services;

use App\Models\Order;
use App\Models\ClientBalanceTransaction;
use Illuminate\Support\Facades\DB;

/**
 * CashbackRewardService
 *
 * Single authority for granting cashback rewards.
 *
 * Guarantees:
 *  - Reward is granted at most ONCE per order (cashback_rewarded flag + DB lock)
 *  - Runs inside its own database transaction
 *  - Creates an immutable ledger entry (ClientBalanceTransaction)
 *  - Updates the client's cached credit_balance
 *  - Stamps cashback_rewarded_at for audit trail
 */
class CashbackRewardService
{
    /**
     * Grant cashback if the order is eligible.
     * Safe to call multiple times – idempotent.
     *
     * @return bool  true if cashback was awarded in this call, false otherwise
     */
    public function rewardIfEligible(Order $order): bool
    {
        // Quick pre-check before acquiring a lock (avoids unnecessary DB overhead)
        if (!$this->isEligible($order)) {
            return false;
        }

        $rewarded = false;

        DB::transaction(function () use ($order, &$rewarded) {
            // Re-fetch with a row lock to prevent concurrent duplicate rewards
            $locked = Order::lockForUpdate()->find($order->id);

            if (!$locked || !$this->isEligible($locked)) {
                return; // Another process already rewarded or order changed
            }

            // ── Create ledger entry ───────────────────────────────────────
            ClientBalanceTransaction::create([
                'client_id'      => $locked->client_id,
                'amount'         => $locked->cashback_amount,
                'type'           => 'cashback_reward',
                'payment_id'     => null,
                'description'    => "Cashback reward for fully paid Order #{$locked->id} ({$locked->cashback_type_snapshot}: {$locked->cashback_value_snapshot})",
                'currency'       => $locked->currency ?? 'TND',
                'reference_type' => 'order',
                'reference_id'   => $locked->id,
            ]);

            // ── Mark order as rewarded ────────────────────────────────────
            DB::table('orders')
                ->where('id', $locked->id)
                ->update([
                    'cashback_rewarded'    => true,
                    'cashback_rewarded_at' => now(),
                    'updated_at'           => now(),
                ]);

            // ── Refresh client cached balance ─────────────────────────────
            // Re-sum from ledger to stay accurate (not just += amount)
            $newBalance = DB::table('client_balance_transactions')
                ->where('client_id', $locked->client_id)
                ->sum('amount');

            DB::table('clients')
                ->where('id', $locked->client_id)
                ->update(['credit_balance' => round((float) $newBalance, 4)]);

            $rewarded = true;
        });

        return $rewarded;
    }

    /**
     * Determine whether a given order is eligible for a cashback reward.
     * Used for eligibility badges in the UI as well.
     */
    public function isEligible(Order $order): bool
    {
        // Already rewarded
        if ($order->cashback_rewarded) {
            return false;
        }

        // Cashback not enabled for this order
        if (!$order->cashback_enabled_snapshot) {
            return false;
        }

        // No reward amount calculated
        if ((float) $order->cashback_amount <= 0) {
            return false;
        }

        // Not fully paid yet
        if ($order->payment_status !== 'paid') {
            return false;
        }

        return true;
    }
}
