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
        Schema::table('rate_cards', function (Blueprint $table) {
            $table->dropColumn('insurance_fee');
            $table->string('estimated_days')->nullable()->after('per_kg_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rate_cards', function (Blueprint $table) {
            $table->decimal('insurance_fee', 12, 2)->default(0)->after('per_kg_price');
            $table->dropColumn('estimated_days');
        });
    }
};
