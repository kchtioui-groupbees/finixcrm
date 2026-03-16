<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add credit_balance to clients
        Schema::table('clients', function (Blueprint $table) {
            $table->decimal('credit_balance', 15, 2)->default(0)->after('email');
        });

        // Create balance transactions table (The Ledger)
        Schema::create('client_balance_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 15, 2); // Positive for credit, negative for debit
            $table->string('type'); // overpayment, manual_adjustment, usage, refund
            $table->foreignId('payment_id')->nullable()->constrained()->onDelete('set null');
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // Update payment_allocations to allow linking to a balance transaction instead of a payment
        Schema::table('payment_allocations', function (Blueprint $table) {
            $table->foreignId('payment_id')->nullable()->change(); // Already exists but make it nullable if it wasn't
            $table->foreignId('balance_transaction_id')->nullable()->after('payment_id')->constrained('client_balance_transactions')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_allocations', function (Blueprint $table) {
            $table->dropForeign(['balance_transaction_id']);
            $table->dropColumn('balance_transaction_id');
        });

        Schema::dropIfExists('client_balance_transactions');

        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn('credit_balance');
        });
    }
};
