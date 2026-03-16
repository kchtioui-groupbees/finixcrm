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
            $table->boolean('warranty_enabled')->default(false);
            $table->integer('warranty_duration_days')->nullable();
            $table->string('warranty_type')->nullable();
            $table->text('warranty_terms')->nullable();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->boolean('warranty_enabled')->default(false);
            $table->integer('warranty_duration_days')->nullable();
            $table->string('warranty_start_mode')->default('purchase_date'); // purchase_date, activation_date, custom_date
            $table->date('warranty_start_date')->nullable();
            $table->date('warranty_end_date')->nullable();
            $table->text('warranty_terms_snapshot')->nullable();
        });

        Schema::create('warranty_claims', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->string('subject');
            $table->text('description');
            $table->string('status')->default('open'); // open, processing, approved, rejected, resolved
            $table->text('admin_notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warranty_claims');
        
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'warranty_enabled', 
                'warranty_duration_days', 
                'warranty_start_mode', 
                'warranty_start_date', 
                'warranty_end_date', 
                'warranty_terms_snapshot'
            ]);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'warranty_enabled', 
                'warranty_duration_days', 
                'warranty_type', 
                'warranty_terms'
            ]);
        });
    }
};
