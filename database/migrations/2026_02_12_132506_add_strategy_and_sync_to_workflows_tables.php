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
        Schema::table('workflows', function (Blueprint $table) {
            $table->string('approved_status')->nullable()->after('is_active');
            $table->string('rejected_status')->nullable()->after('approved_status');
        });

        Schema::table('workflow_stages', function (Blueprint $table) {
            $table->string('approval_strategy')->default('all_must_approve')->after('is_parallel'); // 'all_must_approve', 'any_can_approve'
            $table->boolean('allow_self_approval')->default(true)->after('approval_strategy');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('workflows', function (Blueprint $table) {
            $table->dropColumn(['approved_status', 'rejected_status']);
        });

        Schema::table('workflow_stages', function (Blueprint $table) {
            $table->dropColumn(['approval_strategy', 'allow_self_approval']);
        });
    }
};
