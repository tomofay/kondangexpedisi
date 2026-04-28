<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shipment_trackings', function (Blueprint $table) {
            $table->decimal('gps_lat', 10, 7)->nullable()->after('location');
            $table->decimal('gps_lng', 10, 7)->nullable()->after('gps_lat');
            $table->decimal('gps_accuracy_m', 8, 2)->nullable()->after('gps_lng');
        });
    }

    public function down(): void
    {
        Schema::table('shipment_trackings', function (Blueprint $table) {
            $table->dropColumn(['gps_lat', 'gps_lng', 'gps_accuracy_m']);
        });
    }
};
