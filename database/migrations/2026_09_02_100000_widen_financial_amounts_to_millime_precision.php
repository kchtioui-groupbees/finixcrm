<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * TND is quoted to 3 decimal places (millimes), but every money column in
 * this app was declared decimal(_, 2) — so a genuine 3rd-decimal amount
 * was silently truncated or rounded at the database layer regardless of
 * what PHP/Eloquent did with it (Order::price even carried a 'decimal:3'
 * Eloquent cast already, while its actual column only stored 2 decimals —
 * a real, pre-existing precision bug this migration fixes). Verified
 * directly against MySQL before writing this migration:
 *   DECIMAL(10,2): 45.555 -> 45.56, 0.001 -> 0.00 (lost entirely!), 0.005 -> 0.01
 *   DECIMAL(12,3): 45.555 -> 45.555, 0.001 -> 0.001, 0.005 -> 0.005  (exact)
 *
 * This widens every money column touched by payments, allocations,
 * balances, cashback and refunds to 3 decimal places. Widening a
 * DECIMAL's scale is always safe for existing data — every value
 * currently stored at 2 decimals is exactly representable at 3 (e.g.
 * 45.53 becomes 45.530, not approximated). No data is altered or lost by
 * running this migration.
 *
 * down() reverts the scale back to 2 decimals. That direction is a real,
 * lossy rounding for any value that has picked up a genuine 3rd-decimal
 * digit since this migration ran — an inherent, expected property of
 * reverting a precision-widening change, not a bug in this migration.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('price', 12, 3)->change();
            $table->decimal('renewal_price', 12, 3)->nullable()->change();
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->decimal('amount', 12, 3)->change();
        });

        Schema::table('payment_allocations', function (Blueprint $table) {
            $table->decimal('amount', 12, 3)->change();
        });

        Schema::table('client_balance_transactions', function (Blueprint $table) {
            $table->decimal('amount', 15, 3)->change();
        });

        Schema::table('clients', function (Blueprint $table) {
            $table->decimal('credit_balance', 15, 3)->default(0)->change();
        });

        Schema::table('products', function (Blueprint $table) {
            $table->decimal('cashback_value', 12, 3)->default(0)->change();
            $table->decimal('default_renewal_price', 12, 3)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('price', 10, 2)->change();
            $table->decimal('renewal_price', 10, 2)->nullable()->change();
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->decimal('amount', 10, 2)->change();
        });

        Schema::table('payment_allocations', function (Blueprint $table) {
            $table->decimal('amount', 10, 2)->change();
        });

        Schema::table('client_balance_transactions', function (Blueprint $table) {
            $table->decimal('amount', 15, 2)->change();
        });

        Schema::table('clients', function (Blueprint $table) {
            $table->decimal('credit_balance', 15, 2)->default(0)->change();
        });

        Schema::table('products', function (Blueprint $table) {
            $table->decimal('cashback_value', 10, 2)->default(0)->change();
            $table->decimal('default_renewal_price', 10, 2)->nullable()->change();
        });
    }
};
