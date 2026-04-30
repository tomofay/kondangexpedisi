<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Migrate existing COD payments to 'cash'
        DB::table('payments')->where('method', 'cod')->update(['method' => 'cash']);

        // 2. Remove COD fields from shipments
        Schema::table('shipments', function (Blueprint $table) {
            if (Schema::hasColumn('shipments', 'is_cod')) {
                $table->dropColumn('is_cod');
            }
            if (Schema::hasColumn('shipments', 'cod_amount')) {
                $table->dropColumn('cod_amount');
            }
        });

        // 3. Cleanup zone references (safety — should already be removed by prior migration)
        Schema::disableForeignKeyConstraints();

        if (Schema::hasTable('shipments') && Schema::hasColumn('shipments', 'zone_id')) {
            Schema::table('shipments', function (Blueprint $table) {
                $table->dropForeign(['zone_id']);
                $table->dropColumn('zone_id');
            });
        }

        if (Schema::hasTable('branches') && Schema::hasColumn('branches', 'zone_id')) {
            Schema::table('branches', function (Blueprint $table) {
                $table->dropForeign(['zone_id']);
                $table->dropColumn('zone_id');
            });
        }

        Schema::dropIfExists('zones');

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        // COD fields restoration
        Schema::table('shipments', function (Blueprint $table) {
            if (! Schema::hasColumn('shipments', 'is_cod')) {
                $table->boolean('is_cod')->default(false)->after('total_amount');
            }
            if (! Schema::hasColumn('shipments', 'cod_amount')) {
                $table->decimal('cod_amount', 12, 2)->default(0)->after('is_cod');
            }
        });
    }
};
