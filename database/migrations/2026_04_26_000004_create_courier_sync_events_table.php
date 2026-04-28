<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courier_sync_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('courier_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('shipment_id')->nullable()->constrained('shipments')->nullOnDelete();
            $table->string('client_event_id', 120);
            $table->enum('event_type', ['transition_status', 'tracking_event']);
            $table->enum('status', ['applied', 'duplicate', 'failed']);
            $table->json('payload')->nullable();
            $table->text('processing_error')->nullable();
            $table->timestamp('occurred_at')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();

            $table->unique(['courier_id', 'client_event_id']);
            $table->index(['courier_id', 'status']);
            $table->index(['shipment_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courier_sync_events');
    }
};
