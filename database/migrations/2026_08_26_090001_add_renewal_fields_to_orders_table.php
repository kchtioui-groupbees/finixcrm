<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->boolean('renewable')->default(false)->after('cashback_reversed');
            $table->string('renewal_interval_unit')->nullable()->after('renewable'); // day, month, year
            $table->unsignedInteger('renewal_interval_value')->nullable()->after('renewal_interval_unit');
            $table->decimal('renewal_price', 10, 2)->nullable()->after('renewal_interval_value');
            $table->date('next_due_date')->nullable()->after('renewal_price');

            $table->index('next_due_date');
            $table->index(['renewable', 'next_due_date']);
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['renewable', 'next_due_date']);
            $table->dropIndex(['next_due_date']);
            $table->dropColumn([
                'renewable',
                'renewal_interval_unit',
                'renewal_interval_value',
                'renewal_price',
                'next_due_date',
            ]);
        });
    }
};
