<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;

/**
 * CashbackCalculationService
 *
 * Handles:
 *  - Snapshotting cashback settings from a Product into an Order
 *  - Computing the cashback amount (fixed or percentage)
 *
 * Called at order creation. All values are frozen on the order so that
 * future product edits never retroactively change past cashback awards.
 */
class CashbackCalculationService
{
    /**
     * Copy cashback configuration from a Product into the given Order model.
     * Does NOT save the order – the caller must persist it.
     */
    public function applySnapshotFromProduct(Order $order, Product $product): void
    {
        $order->cashback_enabled_snapshot = (bool) $product->cashback_enabled;
        $order->cashback_type_snapshot    = $product->cashback_type;
        $order->cashback_value_snapshot   = (float) $product->cashback_value;
        $order->cashback_amount           = $this->computeAmount($order);
        $order->cashback_rewarded         = false;
        $order->cashback_rewarded_at      = null;
        $order->cashback_reversed         = false;
    }

    /**
     * (Re)compute the cashback amount from the snapshot already stored on the order.
     * Returns the computed amount as a float (does NOT save the order).
     */
    public function computeAmount(Order $order): float
    {
        if (!$order->cashback_enabled_snapshot) {
            return 0.0;
        }

        $value = (float) $order->cashback_value_snapshot;
        $price = (float) $order->price;

        if ($value <= 0 || $price <= 0) {
            return 0.0;
        }

        $amount = match ($order->cashback_type_snapshot) {
            'fixed'      => $value,
            'percentage' => round($price * $value / 100, $this->precision($order->currency)),
            default      => 0.0,
        };

        // Safeguard: cannot exceed order total
        $amount = min($amount, $price);

        return max(0.0, round($amount, $this->precision($order->currency)));
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    private function precision(?string $currency): int
    {
        return $currency === 'TND' ? 3 : 2;
    }
}
