<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('label');
            $table->boolean('requires_confirmation')->default(true);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // Seed the methods already hardcoded in the payment form, plus the
        // cash / internal-balance methods that never needed confirmation.
        $now = now();
        DB::table('payment_methods')->insert([
            ['key' => 'wafacash', 'label' => 'Wafacash', 'requires_confirmation' => true, 'is_active' => true, 'sort_order' => 10, 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'izi_zitouna', 'label' => 'IZI Zitouna', 'requires_confirmation' => true, 'is_active' => true, 'sort_order' => 20, 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'virement_bancaire', 'label' => 'Virement Bancaire', 'requires_confirmation' => true, 'is_active' => true, 'sort_order' => 30, 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'mandat', 'label' => 'Mandat', 'requires_confirmation' => true, 'is_active' => true, 'sort_order' => 40, 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'd17', 'label' => 'D17', 'requires_confirmation' => true, 'is_active' => true, 'sort_order' => 50, 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'flouci', 'label' => 'Flouci', 'requires_confirmation' => true, 'is_active' => true, 'sort_order' => 60, 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'redotpay', 'label' => 'Redotpay', 'requires_confirmation' => true, 'is_active' => true, 'sort_order' => 70, 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'binance', 'label' => 'Binance', 'requires_confirmation' => true, 'is_active' => true, 'sort_order' => 80, 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'paypal', 'label' => 'PayPal', 'requires_confirmation' => true, 'is_active' => true, 'sort_order' => 90, 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'especes', 'label' => 'Espèces', 'requires_confirmation' => false, 'is_active' => true, 'sort_order' => 100, 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'solde_interne', 'label' => 'Solde interne', 'requires_confirmation' => false, 'is_active' => true, 'sort_order' => 110, 'created_at' => $now, 'updated_at' => $now],
            ['key' => 'other', 'label' => 'Other', 'requires_confirmation' => true, 'is_active' => true, 'sort_order' => 999, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};
