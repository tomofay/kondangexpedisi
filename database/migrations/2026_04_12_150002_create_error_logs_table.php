<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('error_logs', function (Blueprint $table) {
            $table->id();
            $table->string('error_type'); // 'exception', 'warning', 'critical'
            $table->string('module'); // 'payment', 'shipment', 'auth', etc
            $table->text('message');
            $table->text('stack_trace')->nullable();
            $table->json('context')->nullable(); // Additional data
            $table->string('file_name')->nullable();
            $table->integer('line_number')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullableOnDelete();
            $table->string('severity', 20); // 'low', 'medium', 'high', 'critical'
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
            $table->index(['severity', 'created_at']);
            $table->index(['module', 'error_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('error_logs');
    }
};
