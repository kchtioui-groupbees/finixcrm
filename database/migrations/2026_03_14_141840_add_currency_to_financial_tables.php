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
        Schema::table('clients', function (Blueprint $table) {
            $table->string('currency', 3)->default('USD')->after('tags');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->string('currency', 3)->default('USD')->after('status');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->string('currency', 3)->default('USD')->after('status');
        });

        Schema::table('client_balance_transactions', function (Blueprint $table) {
            $table->string('currency', 3)->default('USD')->after('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn('currency');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('currency');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('currency');
        });

        Schema::table('client_balance_transactions', function (Blueprint $table) {
            $table->dropColumn('currency');
        });
    }
};
