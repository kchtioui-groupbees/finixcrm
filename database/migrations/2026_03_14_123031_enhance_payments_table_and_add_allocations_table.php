<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->unsignedBigInteger('client_id')->after('id')->nullable();
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
            $table->unsignedBigInteger('order_id')->nullable()->change();
            $table->string('type')->default('specific_order')->after('status'); // specific_order, balance_allocation
            $table->text('internal_notes')->nullable()->after('type');
        });

        Schema::create('payment_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained()->onDelete('cascade');
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->timestamps();
        });
        
        // Data Migration: Link existing payments to their order's client
        $payments = DB::table('payments')->get();
        foreach ($payments as $payment) {
            if ($payment->order_id) {
                $order = DB::table('orders')->where('id', $payment->order_id)->first();
                if ($order) {
                    DB::table('payments')->where('id', $payment->id)->update([
                        'client_id' => data_get($order, 'client_id')
                    ]);
                    
                    // Also create an initial allocation for existing payments
                    DB::table('payment_allocations')->insert([
                        'payment_id' => $payment->id,
                        'order_id' => $payment->order_id,
                        'amount' => $payment->amount,
                        'created_at' => $payment->created_at,
                        'updated_at' => $payment->updated_at,
                    ]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_allocations');
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['client_id']);
            $table->dropColumn(['client_id', 'type']);
            $table->unsignedBigInteger('order_id')->nullable(false)->change();
        });
    }
};
