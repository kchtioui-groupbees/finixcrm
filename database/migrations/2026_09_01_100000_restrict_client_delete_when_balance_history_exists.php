<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Pre-commit audit finding: client_balance_transactions.client_id was
 * declared onDelete('cascade'), so deleting a Client would silently and
 * permanently destroy their entire balance/refund/avoir history — a hard
 * violation of "never delete refund/credit history". This additive
 * migration only swaps the FK's delete rule to restrict; it changes no
 * data and drops no columns.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('client_balance_transactions', function (Blueprint $table) {
            $table->dropForeign(['client_id']);
        });

        Schema::table('client_balance_transactions', function (Blueprint $table) {
            $table->foreign('client_id')
                ->references('id')->on('clients')
                ->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::table('client_balance_transactions', function (Blueprint $table) {
            $table->dropForeign(['client_id']);
        });

        Schema::table('client_balance_transactions', function (Blueprint $table) {
            $table->foreign('client_id')
                ->references('id')->on('clients')
                ->onDelete('cascade');
        });
    }
};
