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
            $table->dropColumn([
                'insurance_amount',
                'admin_fee',
                'auto_insurance_amount',
                'auto_admin_fee'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->decimal('insurance_amount', 12, 2)->default(0)->after('subtotal_amount');
            $table->decimal('admin_fee', 12, 2)->default(0)->after('insurance_amount');
            $table->decimal('auto_insurance_amount', 12, 2)->nullable()->after('auto_subtotal_amount');
            $table->decimal('auto_admin_fee', 12, 2)->nullable()->after('auto_insurance_amount');
        });
    }
};
