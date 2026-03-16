<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── 1. Orders table: cashback snapshot + audit fields ──────────
        Schema::table('orders', function (Blueprint $table) {
            // These columns may already exist – skip if they do.
            if (!Schema::hasColumn('orders', 'cashback_enabled_snapshot')) {
                $table->boolean('cashback_enabled_snapshot')->default(false)->after('cashback_rewarded');
            }
            if (!Schema::hasColumn('orders', 'cashback_type_snapshot')) {
                $table->string('cashback_type_snapshot')->nullable()->after('cashback_enabled_snapshot');
            }
            if (!Schema::hasColumn('orders', 'cashback_value_snapshot')) {
                $table->decimal('cashback_value_snapshot', 12, 3)->nullable()->after('cashback_type_snapshot');
            }
            if (!Schema::hasColumn('orders', 'cashback_amount')) {
                $table->decimal('cashback_amount', 12, 3)->default(0)->after('cashback_value_snapshot');
            }
            if (!Schema::hasColumn('orders', 'cashback_rewarded_at')) {
                $table->timestamp('cashback_rewarded_at')->nullable()->after('cashback_amount');
            }
            if (!Schema::hasColumn('orders', 'cashback_reversed')) {
                $table->boolean('cashback_reversed')->default(false)->after('cashback_rewarded_at');
            }
        });

        // ── 2. client_balance_transactions: Add reference + currency ───
        Schema::table('client_balance_transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('client_balance_transactions', 'currency')) {
                $table->string('currency', 3)->default('TND')->after('description');
            }
            if (!Schema::hasColumn('client_balance_transactions', 'reference_type')) {
                $table->string('reference_type')->nullable()->after('currency');
            }
            if (!Schema::hasColumn('client_balance_transactions', 'reference_id')) {
                $table->unsignedBigInteger('reference_id')->nullable()->after('reference_type');
            }
            // Index for quick lookups: "find all cashback rewards for order X"
            $table->index(['reference_type', 'reference_id'], 'cbt_reference_index');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'cashback_enabled_snapshot',
                'cashback_type_snapshot',
                'cashback_value_snapshot',
                'cashback_amount',
                'cashback_rewarded_at',
                'cashback_reversed',
            ]);
        });

        Schema::table('client_balance_transactions', function (Blueprint $table) {
            $table->dropIndex('cbt_reference_index');
            $table->dropColumn(['reference_type', 'reference_id']);
        });
    }
};
