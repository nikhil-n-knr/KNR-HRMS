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
            $table->foreignId('team_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('workflow_stages', function (Blueprint $table) {
            $table->dropForeign(['team_id']);
            $table->dropForeign(['department_id']);
            $table->dropColumn(['team_id', 'department_id']);
        });
    }
};
