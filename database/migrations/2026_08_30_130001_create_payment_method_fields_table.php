<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_method_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_method_id')->constrained()->cascadeOnDelete();
            $table->string('label');
            $table->text('value')->nullable(); // never invented — null until an admin fills it in
            $table->string('type')->default('text'); // text, phone, email, link, wallet_address
            $table->boolean('is_public')->default(true);
            $table->boolean('copyable')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['payment_method_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_method_fields');
    }
};
