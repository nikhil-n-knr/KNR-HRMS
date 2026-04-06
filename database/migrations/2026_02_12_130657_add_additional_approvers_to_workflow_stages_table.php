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
        Schema::table('workflow_stages', function (Blueprint $table) {
            $table->json('additional_approvers')->nullable()->after('department_id');
        });
    }

    public function down(): void
    {
        Schema::table('workflow_stages', function (Blueprint $table) {
            $table->dropColumn('additional_approvers');
        });
    }
};
