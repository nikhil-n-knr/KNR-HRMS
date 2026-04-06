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
        Schema::table('crm_deals', function (Blueprint $table) {
            if (!Schema::hasColumn('crm_deals', 'stage_id')) {
                $table->foreignId('stage_id')->nullable()->after('stage')->constrained('pipeline_stages')->onDelete('set null');
            }
            if (!Schema::hasColumn('crm_deals', 'loss_reason')) {
                $table->string('loss_reason')->nullable()->after('stage_id');
            }
            if (!Schema::hasColumn('crm_deals', 'loss_notes')) {
                $table->text('loss_notes')->nullable()->after('loss_reason');
            }
            if (!Schema::hasColumn('crm_deals', 'lost_to_competitor_id')) {
                $table->foreignId('lost_to_competitor_id')->nullable()->after('loss_notes')->constrained('crm_accounts')->onDelete('set null');
            }
            if (!Schema::hasColumn('crm_deals', 'expected_close_date')) {
                $table->date('expected_close_date')->nullable()->after('value');
            }
            if (!Schema::hasColumn('crm_deals', 'win_probability')) {
                $table->integer('win_probability')->default(50)->after('expected_close_date');
            }
            if (!Schema::hasColumn('crm_deals', 'moved_to_stage_at')) {
                $table->date('moved_to_stage_at')->nullable()->after('win_probability');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('crm_deals', function (Blueprint $table) {
            $table->dropForeign(['stage_id']);
            $table->dropForeign(['lost_to_competitor_id']);
            $table->dropColumn([
                'stage_id',
                'loss_reason',
                'loss_notes',
                'lost_to_competitor_id',
                'expected_close_date',
                'win_probability',
                'moved_to_stage_at'
            ]);
        });
    }
};
