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
            $table->index(['branch_id', 'status_id'], 'idx_shipments_branch_status');
            $table->index(['courier_id', 'status_id'], 'idx_shipments_courier_status');
            $table->index(['customer_id', 'created_at'], 'idx_shipments_customer_created');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->index(['shipment_id', 'status'], 'idx_payments_shipment_status');
        });

        Schema::table('shipment_trackings', function (Blueprint $table) {
            $table->index(['shipment_id', 'event_at'], 'idx_trackings_shipment_event');
        });

        Schema::table('audit_logs', function (Blueprint $table) {
            $table->index(['actor_id', 'created_at'], 'idx_audit_actor_created');
            $table->index(['subject_type', 'subject_id'], 'idx_audit_subject');
        });

        Schema::table('admin_tasks', function (Blueprint $table) {
            $table->index(['status', 'task_type', 'assigned_to'], 'idx_tasks_status_type_assignee');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->dropIndex('idx_shipments_branch_status');
            $table->dropIndex('idx_shipments_courier_status');
            $table->dropIndex('idx_shipments_customer_created');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex('idx_payments_shipment_status');
        });

        Schema::table('shipment_trackings', function (Blueprint $table) {
            $table->dropIndex('idx_trackings_shipment_event');
        });

        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropIndex('idx_audit_actor_created');
            $table->dropIndex('idx_audit_subject');
        });

        Schema::table('admin_tasks', function (Blueprint $table) {
            $table->dropIndex('idx_tasks_status_type_assignee');
        });
    }
};
