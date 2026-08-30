<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_methods', function (Blueprint $table) {
            $table->string('category')->nullable()->after('label'); // wallet, agency, bank_transfer, postal_transfer, crypto, card, gateway, cash
            $table->json('currencies')->nullable()->after('category'); // e.g. ["TND"], ["TND","EUR","USD"]
            $table->string('fee_type')->default('fixed')->after('currencies'); // fixed, percentage, unknown
            $table->decimal('fee_value', 10, 3)->nullable()->after('fee_type'); // null when fee_type=unknown — never 0 as a stand-in for "unknown"
            $table->string('fee_paid_by')->default('customer')->after('fee_value'); // customer, merchant
            $table->string('fee_label')->nullable()->after('fee_paid_by'); // shown to admins/clients when the fee is unknown
            $table->json('details')->nullable()->after('fee_label'); // category-specific contact/account info (contacts, RIB, wallet address...)
        });
    }

    public function down(): void
    {
        Schema::table('payment_methods', function (Blueprint $table) {
            $table->dropColumn(['category', 'currencies', 'fee_type', 'fee_value', 'fee_paid_by', 'fee_label', 'details']);
        });
    }
};
