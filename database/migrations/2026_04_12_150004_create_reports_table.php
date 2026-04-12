<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->string('report_type'); // 'kpi_snapshot', 'daily_export', 'weekly_export'
            $table->string('title');
            $table->enum('frequency', ['daily', 'weekly', 'monthly', 'manual'])->default('manual');
            $table->json('recipients'); // email addresses
            $table->json('filters'); // date range, branch, service type, status
            $table->enum('format', ['csv', 'pdf', 'excel'])->default('pdf');
            $table->string('file_path')->nullable(); // S3 or local path
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->foreignId('generated_by')->nullable()->constrained('users')->nullableOnDelete();
            $table->text('error_message')->nullable();
            $table->integer('record_count')->nullable();
            $table->timestamp('generated_at')->nullable();
            $table->timestamp('next_run_at')->nullable();
            $table->timestamps();
            $table->index('report_type');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
