<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        if (Schema::hasTable('rate_cards')) {
            Schema::table('rate_cards', function (Blueprint $table) {
                
                // 1. HAPUS FOREIGN KEY (Berdasarkan data DB kamu)
                // Kita bungkus try-catch agar jika sudah terhapus tidak error
                try {
                    $table->dropForeign(['zone_id']);
                } catch (\Exception $e) {}

                try {
                    $table->dropForeign(['origin_zone_id']);
                } catch (\Exception $e) {}

                try {
                    $table->dropForeign(['destination_zone_id']);
                } catch (\Exception $e) {}

                // 2. HAPUS INDEX (Gunakan try-catch untuk semua kemungkinan nama index)
                $indexesToDrop = [
                    'rate_cards_zone_id_service_type_is_active_index',
                    'rate_cards_route_service_active_idx',
                    'rate_cards_origin_zone_id_destination_zone_id_service_type_is_active_index'
                ];

                foreach ($indexesToDrop as $index) {
                    try {
                        // Raw query lebih aman untuk drop index di MySQL jika nama tidak pasti
                        DB::statement("ALTER TABLE rate_cards DROP INDEX `{$index}`");
                    } catch (\Exception $e) {
                        // Abaikan jika index tidak ada
                    }
                }

                // 3. HAPUS KOLOM LAMA
                $columnsToDrop = ['zone_id', 'origin_zone_id', 'destination_zone_id'];
                foreach ($columnsToDrop as $col) {
                    if (Schema::hasColumn('rate_cards', $col)) {
                        $table->dropColumn($col);
                    }
                }

                // 4. TAMBAH KOLOM BARU (BRANCH)
                if (!Schema::hasColumn('rate_cards', 'origin_branch_id')) {
                    $table->unsignedBigInteger('origin_branch_id')->nullable()->after('id');
                    $table->foreign('origin_branch_id')->references('id')->on('branches')->onDelete('cascade');
                }
                
                if (!Schema::hasColumn('rate_cards', 'destination_branch_id')) {
                    $table->unsignedBigInteger('destination_branch_id')->nullable()->after('origin_branch_id');
                    $table->foreign('destination_branch_id')->references('id')->on('branches')->onDelete('cascade');
                }
            });
        }

        // 5. BERSIHKAN TABEL LAIN
        if (Schema::hasTable('branches') && Schema::hasColumn('branches', 'zone_id')) {
            Schema::table('branches', function (Blueprint $table) {
                try { $table->dropForeign(['zone_id']); } catch (\Exception $e) {}
                $table->dropColumn('zone_id');
            });
        }

        if (Schema::hasTable('shipments') && Schema::hasColumn('shipments', 'zone_id')) {
            Schema::table('shipments', function (Blueprint $table) {
                try { $table->dropForeign(['zone_id']); } catch (\Exception $e) {}
                $table->dropColumn('zone_id');
            });
        }

        // 6. HAPUS TABEL ZONES
        Schema::dropIfExists('zones');

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        // Kosongkan saja untuk migrasi destruktif
    }
};