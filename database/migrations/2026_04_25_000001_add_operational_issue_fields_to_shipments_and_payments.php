<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->enum('processing_status', ['ok', 'needs_manual_review', 'error'])->default('ok')->after('payment_status');
            $table->text('processing_error')->nullable()->after('processing_status');
            $table->enum('pricing_mode', ['auto', 'manual'])->default('auto')->after('processing_error');
            $table->foreignId('manual_override_by')->nullable()->after('pricing_mode')->constrained('users')->nullOnDelete();
            $table->text('manual_override_reason')->nullable()->after('manual_override_by');
            $table->timestamp('manual_override_at')->nullable()->after('manual_override_reason');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->enum('processing_status', ['ok', 'needs_manual_review', 'error'])->default('ok')->after('status');
            $table->text('processing_error')->nullable()->after('processing_status');
            $table->foreignId('manual_override_by')->nullable()->after('processing_error')->constrained('users')->nullOnDelete();
            $table->text('manual_override_reason')->nullable()->after('manual_override_by');
            $table->timestamp('manual_override_at')->nullable()->after('manual_override_reason');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('manual_override_by');
            $table->dropColumn([
                'processing_status',
                'processing_error',
                'manual_override_reason',
                'manual_override_at',
            ]);
        });

        Schema::table('shipments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('manual_override_by');
            $table->dropColumn([
                'processing_status',
                'processing_error',
                'pricing_mode',
                'manual_override_reason',
                'manual_override_at',
            ]);
        });
    }
};