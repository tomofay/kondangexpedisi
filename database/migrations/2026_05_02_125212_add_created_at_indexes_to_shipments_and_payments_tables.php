<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->index('created_at', 'idx_shipments_created_at');
            $table->index(['status_id', 'created_at'], 'idx_shipments_status_created');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->index('created_at', 'idx_payments_created_at');
            $table->index(['status', 'created_at'], 'idx_payments_status_created');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->dropIndex('idx_shipments_created_at');
            $table->dropIndex('idx_shipments_status_created');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex('idx_payments_created_at');
            $table->dropIndex('idx_payments_status_created');
        });
    }
};
