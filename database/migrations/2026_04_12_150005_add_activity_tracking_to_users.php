<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('last_login_at')->nullable()->after('email_verified_at');
            $table->timestamp('last_activity_at')->nullable()->after('last_login_at');
            $table->string('last_login_ip')->nullable()->after('last_activity_at');
            $table->json('permissions')->nullable()->after('role'); // Role-based permissions
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['last_login_at', 'last_activity_at', 'last_login_ip', 'permissions']);
        });
    }
};
