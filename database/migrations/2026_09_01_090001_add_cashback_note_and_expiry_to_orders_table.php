<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->text('cashback_note')->nullable()->after('cashback_reversed');
            $table->date('cashback_expires_at')->nullable()->after('cashback_note');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['cashback_note', 'cashback_expires_at']);
        });
    }
};
