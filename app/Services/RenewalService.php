<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use Carbon\Carbon;
use InvalidArgumentException;

/**
 * Single source of truth for renewal date math.
 *
 * next_due_date is always derived from the previous due date (or the purchase
 * date, for the very first cycle) plus the interval — never from "now" — so
 * the billing anchor (e.g. "the 26th") never drifts because a payment was
 * logged a few days early or late.
 */
class RenewalService
{
    public const UNITS = ['day', 'month', 'year'];

    public function calculateNextDueDate(Carbon $from, string $unit, int $value): Carbon
    {
        return match ($unit) {
            'day' => $from->copy()->addDays($value),
            'month' => $from->copy()->addMonthsNoOverflow($value),
            'year' => $from->copy()->addYearsNoOverflow($value),
            default => throw new InvalidArgumentException("Unsupported renewal_interval_unit [{$unit}]"),
        };
    }

    /**
     * Copy the product's renewal defaults onto a fresh order and compute its
     * first next_due_date, unless the order already carries explicit
     * (user-overridden) renewal settings.
     */
    public function applyProductDefaults(Order $order, Product $product): void
    {
        if (is_null($order->getAttribute('renewable'))) {
            $order->renewable = (bool) $product->renewable;
        }

        if (is_null($order->renewal_interval_unit)) {
            $order->renewal_interval_unit = $product->renewal_interval_unit;
        }

        if (is_null($order->renewal_interval_value)) {
            $order->renewal_interval_value = $product->renewal_interval_value;
        }

        if (is_null($order->renewal_price)) {
            $order->renewal_price = $product->default_renewal_price;
        }

        if ($order->renewable && $order->renewal_interval_unit && $order->renewal_interval_value) {
            $base = $order->purchase_date ? Carbon::parse($order->purchase_date) : now();
            $order->next_due_date = $this->calculateNextDueDate(
                $base,
                $order->renewal_interval_unit,
                (int) $order->renewal_interval_value
            );
        } else {
            $order->next_due_date = null;
        }
    }

    /**
     * Record that a renewal has just been paid: advance next_due_date from
     * its current value by one interval. Does not touch payments or
     * allocations — callers are responsible for recording the payment.
     */
    public function markRenewed(Order $order): Order
    {
        if (!$order->renewable || !$order->renewal_interval_unit || !$order->renewal_interval_value) {
            throw new InvalidArgumentException('Cannot renew an order that is not renewable or has no interval configured.');
        }

        $base = $order->next_due_date ? Carbon::parse($order->next_due_date) : now();

        $order->next_due_date = $this->calculateNextDueDate(
            $base,
            $order->renewal_interval_unit,
            (int) $order->renewal_interval_value
        );
        $order->save();

        return $order;
    }

    /**
     * Stop renewal for this order only — never touches the product template.
     */
    public function stopRenewal(Order $order): Order
    {
        $order->renewable = false;
        $order->save();

        return $order;
    }
}
