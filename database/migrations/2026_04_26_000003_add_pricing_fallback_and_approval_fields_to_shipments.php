<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->decimal('auto_subtotal_amount', 12, 2)->nullable()->after('total_amount');
            $table->decimal('auto_insurance_amount', 12, 2)->nullable()->after('auto_subtotal_amount');
            $table->decimal('auto_admin_fee', 12, 2)->nullable()->after('auto_insurance_amount');
            $table->decimal('auto_total_amount', 12, 2)->nullable()->after('auto_admin_fee');
            $table->decimal('corrected_total_amount', 12, 2)->nullable()->after('auto_total_amount');
            $table->enum('pricing_approval_status', ['not_required', 'pending', 'approved', 'rejected'])
                ->default('not_required')
                ->after('pricing_mode');
            $table->foreignId('pricing_approved_by')
                ->nullable()
                ->after('pricing_approval_status')
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('pricing_approved_at')->nullable()->after('pricing_approved_by');
        });
    }

    public function down(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('pricing_approved_by');
            $table->dropColumn([
                'auto_subtotal_amount',
                'auto_insurance_amount',
                'auto_admin_fee',
                'auto_total_amount',
                'corrected_total_amount',
                'pricing_approval_status',
                'pricing_approved_at',
            ]);
        });
    }
};
