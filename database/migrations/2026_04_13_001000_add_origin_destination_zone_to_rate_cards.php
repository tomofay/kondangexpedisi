<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rate_cards', function (Blueprint $table) {
            $table->foreignId('origin_zone_id')->nullable()->after('zone_id')->constrained('zones')->nullOnDelete();
            $table->foreignId('destination_zone_id')->nullable()->after('origin_zone_id')->constrained('zones')->nullOnDelete();
            $table->index(['origin_zone_id', 'destination_zone_id', 'service_type', 'is_active'], 'rate_cards_route_service_active_idx');
        });

        DB::table('rate_cards')
            ->whereNull('origin_zone_id')
            ->update([
                'origin_zone_id' => DB::raw('zone_id'),
                'destination_zone_id' => DB::raw('zone_id'),
            ]);
    }

    public function down(): void
    {
        Schema::table('rate_cards', function (Blueprint $table) {
            $table->dropIndex('rate_cards_route_service_active_idx');
            $table->dropConstrainedForeignId('destination_zone_id');
            $table->dropConstrainedForeignId('origin_zone_id');
        });
    }
};
