<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipment_tracking_proofs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipment_id')->constrained('shipments')->cascadeOnDelete();
            $table->foreignId('tracking_id')->constrained('shipment_trackings')->cascadeOnDelete();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('proof_type', ['pickup_photo', 'handover_photo', 'recipient_signature']);
            $table->string('file_path');
            $table->string('file_mime', 120)->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->string('file_hash', 64);
            $table->decimal('gps_lat', 10, 7)->nullable();
            $table->decimal('gps_lng', 10, 7)->nullable();
            $table->decimal('gps_accuracy_m', 8, 2)->nullable();
            $table->timestamp('captured_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['tracking_id', 'proof_type', 'file_hash']);
            $table->index(['shipment_id', 'proof_type']);
            $table->index(['uploaded_by', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipment_tracking_proofs');
    }
};
