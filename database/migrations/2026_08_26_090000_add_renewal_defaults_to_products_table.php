<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('renewable')->default(false)->after('cashback_value');
            $table->string('renewal_interval_unit')->nullable()->after('renewable'); // day, month, year
            $table->unsignedInteger('renewal_interval_value')->nullable()->after('renewal_interval_unit');
            $table->decimal('default_renewal_price', 10, 2)->nullable()->after('renewal_interval_value');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'renewable',
                'renewal_interval_unit',
                'renewal_interval_value',
                'default_renewal_price',
            ]);
        });
    }
};
