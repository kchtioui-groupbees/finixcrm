<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_methods', function (Blueprint $table) {
            $table->text('description')->nullable()->after('category');
            $table->string('logo_path')->nullable()->after('description');
            $table->text('instructions')->nullable()->after('logo_path');
            $table->boolean('is_public')->default(true)->after('is_active');
            $table->boolean('proof_required')->default(false)->after('requires_confirmation');
            $table->boolean('reference_required')->default(false)->after('proof_required');
            $table->string('fee_currency', 3)->nullable()->after('fee_value');
            $table->timestamp('archived_at')->nullable()->after('sort_order');
        });
    }

    public function down(): void
    {
        Schema::table('payment_methods', function (Blueprint $table) {
            $table->dropColumn([
                'description', 'logo_path', 'instructions', 'is_public',
                'proof_required', 'reference_required', 'fee_currency', 'archived_at',
            ]);
        });
    }
};
