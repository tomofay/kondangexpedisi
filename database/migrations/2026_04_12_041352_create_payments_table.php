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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipment_id')->constrained('shipments')->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();

            $table->enum('method', ['midtrans', 'cash', 'transfer', 'e_wallet', 'cod'])->default('midtrans');
            $table->enum('status', ['pending', 'settlement', 'deny', 'expire', 'cancel', 'refund', 'failed'])->default('pending');
            $table->decimal('amount', 12, 2);

            $table->string('midtrans_order_id')->nullable()->unique();
            $table->string('midtrans_transaction_id')->nullable();
            $table->text('snap_token')->nullable();
            $table->text('snap_redirect_url')->nullable();

            $table->string('payment_type')->nullable();
            $table->string('bank_name', 50)->nullable();
            $table->string('va_number', 60)->nullable();
            $table->string('fraud_status', 30)->nullable();
            $table->string('signature_key')->nullable();

            $table->timestamp('transaction_time')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->json('gateway_payload')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['shipment_id', 'status']);
            $table->index(['midtrans_transaction_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
