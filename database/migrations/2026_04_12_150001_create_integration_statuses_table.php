<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('integration_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('service_name')->unique(); // 'midtrans', 'email', 'backup'
            $table->enum('status', ['healthy', 'degraded', 'down'])->default('healthy');
            $table->integer('success_count')->default(0);
            $table->integer('failure_count')->default(0);
            $table->timestamp('last_success_at')->nullable();
            $table->timestamp('last_failure_at')->nullable();
            $table->text('last_error_message')->nullable();
            $table->json('metadata')->nullable(); // version, endpoint, etc
            $table->timestamps();
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('integration_statuses');
    }
};
