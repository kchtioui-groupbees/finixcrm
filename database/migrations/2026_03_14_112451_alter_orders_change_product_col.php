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
        // First clear out orders to prevent constraint violations
        \App\Models\Order::query()->delete();

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('product');
            $table->foreignId('product_id')->after('client_id')->constrained('products')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->dropColumn('product_id');
            $table->string('product')->after('client_id');
        });
    }
};
