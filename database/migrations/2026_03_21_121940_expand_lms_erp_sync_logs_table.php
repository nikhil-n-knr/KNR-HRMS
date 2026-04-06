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
        Schema::table('lms_erp_sync_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('lms_erp_sync_logs', 'direction')) {
                $table->string('direction', 20)->default('inbound')->after('institution_id');
            }
            if (!Schema::hasColumn('lms_erp_sync_logs', 'entity_type')) {
                $table->string('entity_type')->nullable()->after('direction');
            }
            if (!Schema::hasColumn('lms_erp_sync_logs', 'status')) {
                $table->string('status', 20)->default('pending')->after('entity_type');
            }
            if (!Schema::hasColumn('lms_erp_sync_logs', 'records_failed')) {
                $table->integer('records_failed')->default(0)->after('records_processed');
            }
            if (!Schema::hasColumn('lms_erp_sync_logs', 'error_log')) {
                $table->json('error_log')->nullable()->after('records_failed');
            }
            if (!Schema::hasColumn('lms_erp_sync_logs', 'triggered_by')) {
                $table->unsignedBigInteger('triggered_by')->nullable()->after('error_log');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lms_erp_sync_logs', function (Blueprint $table) {
            $table->dropColumn([
                'direction', 'entity_type', 'status', 'records_failed', 'error_log', 'triggered_by'
            ]);
        });
    }
};
