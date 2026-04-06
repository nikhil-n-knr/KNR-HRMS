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
        if (!Schema::hasColumn('workflow_stages', 'auto_approve_after_hours')) {
            Schema::table('workflow_stages', function (Blueprint $table) {
                $table->integer('auto_approve_after_hours')->nullable()->after('can_edit');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('workflow_stages', function (Blueprint $table) {
            $table->dropColumn('auto_approve_after_hours');
        });
    }
};
