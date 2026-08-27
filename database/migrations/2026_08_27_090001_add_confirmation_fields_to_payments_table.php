<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('reference')->nullable()->after('payment_method');
            $table->foreignId('created_by')->nullable()->after('internal_notes')->constrained('users')->nullOnDelete();
            $table->timestamp('confirmed_at')->nullable()->after('created_by');
            $table->foreignId('confirmed_by')->nullable()->after('confirmed_at')->constrained('users')->nullOnDelete();

            $table->index('status');
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex(['status', 'created_at']);
            $table->dropIndex(['status']);
            $table->dropConstrainedForeignId('confirmed_by');
            $table->dropColumn('confirmed_at');
            $table->dropConstrainedForeignId('created_by');
            $table->dropColumn('reference');
        });
    }
};
