<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Single source of truth for turning a "client says they paid" pending
 * payment into a definitive, financially-effective one — or rejecting it.
 *
 * The client's own declaration that a manual-method payment was sent is not
 * proof the money arrived. For methods that require confirmation, the
 * payment is created with status=pending and has NO financial effect
 * (PaymentAllocationService already only ever considers status=completed
 * payments, and the renewal flow never advances next_due_date for a
 * pending payment). Confirmation via this service is the only event that
 * triggers the definitive financial consequences: allocation reconciliation
 * for a regular order/balance payment, or advancing next_due_date for a
 * renewal payment — done atomically so a confirmed payment can never end up
 * without its renewal, or vice versa, and so a payment can never be
 * confirmed (and its renewal triggered) twice.
 */
class PaymentConfirmationService
{
    public function confirm(Payment $payment, User $confirmedBy): Payment
    {
        return DB::transaction(function () use ($payment, $confirmedBy) {
            $locked = Payment::whereKey($payment->id)->lockForUpdate()->firstOrFail();

            if ($locked->status !== 'pending') {
                throw new RuntimeException('This payment is not pending confirmation and cannot be confirmed again.');
            }

            $locked->status = 'completed';
            $locked->confirmed_at = now();
            $locked->confirmed_by = $confirmedBy->id;
            $locked->save();

            if ($locked->type === 'renewal' && $locked->order && $locked->order->renewable) {
                app(RenewalService::class)->markRenewed($locked->order);
            } elseif ($locked->client_id) {
                app(PaymentAllocationService::class)->reallocateForClient((int) $locked->client_id);
            }

            return $locked->fresh();
        });
    }

    public function reject(Payment $payment, User $rejectedBy, ?string $reason = null): Payment
    {
        return DB::transaction(function () use ($payment, $rejectedBy, $reason) {
            $locked = Payment::whereKey($payment->id)->lockForUpdate()->firstOrFail();

            if ($locked->status !== 'pending') {
                throw new RuntimeException('This payment is not pending confirmation and cannot be rejected.');
            }

            $locked->status = 'rejected';
            $locked->rejected_at = now();
            $locked->rejected_by = $rejectedBy->id;
            if ($reason) {
                $locked->internal_notes = trim(($locked->internal_notes ? $locked->internal_notes . "\n" : '') . $reason);
            }
            $locked->save();

            return $locked->fresh();
        });
    }
}
