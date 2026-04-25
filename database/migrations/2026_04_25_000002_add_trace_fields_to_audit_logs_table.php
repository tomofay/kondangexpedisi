<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->string('source', 40)->default('system_automatic')->after('notes');
            $table->boolean('is_manual_correction')->default(false)->after('source');
            $table->string('correction_reference', 120)->nullable()->after('is_manual_correction');
            $table->json('changed_fields')->nullable()->after('correction_reference');

            $table->index(['source', 'created_at']);
            $table->index(['is_manual_correction', 'created_at']);
            $table->index(['correction_reference']);
        });
    }

    public function down(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropIndex(['source', 'created_at']);
            $table->dropIndex(['is_manual_correction', 'created_at']);
            $table->dropIndex(['correction_reference']);

            $table->dropColumn([
                'source',
                'is_manual_correction',
                'correction_reference',
                'changed_fields',
            ]);
        });
    }
};