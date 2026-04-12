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
        Schema::create('rate_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('zone_id')->constrained('zones')->cascadeOnDelete();
            $table->enum('service_type', ['regular', 'express', 'same_day', 'economy'])->default('regular');
            $table->decimal('min_weight_kg', 10, 2);
            $table->decimal('max_weight_kg', 10, 2)->nullable();
            $table->decimal('base_price', 12, 2);
            $table->decimal('per_kg_price', 12, 2)->default(0);
            $table->decimal('insurance_fee', 12, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['zone_id', 'service_type', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rate_cards');
    }
};
