<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('must_change_password')->default(false)->after('password');
            $table->timestamp('last_login_at')->nullable()->after('must_change_password');
        });

        Schema::table('clients', function (Blueprint $table) {
            $table->string('finix_email')->nullable()->unique()->after('email');
            $table->string('status')->default('active')->after('finix_email'); // active, inactive
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn(['finix_email', 'status']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['must_change_password', 'last_login_at']);
        });
    }
};
