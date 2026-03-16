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
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('cashback_enabled')->default(false)->after('is_active');
            $table->string('cashback_type')->default('percentage')->after('cashback_enabled'); // 'percentage' or 'fixed'
            $table->decimal('cashback_value', 10, 2)->default(0)->after('cashback_type');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->boolean('cashback_rewarded')->default(false)->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['cashback_enabled', 'cashback_type', 'cashback_value']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('cashback_rewarded');
        });
    }
};
