<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('timesheets', function (Blueprint $table) {
            $table->foreignId('project_id')->nullable()->after('date')->constrained()->nullOnDelete();
            // We keep project_name for legacy or textual backup, or make it nullable?
            // Existing migration had it as string. We'll leave it but make it nullable if not already.
            $table->string('project_name')->nullable()->change(); 
        });
    }

    public function down(): void
    {
        Schema::table('timesheets', function (Blueprint $table) {
            $table->dropForeign(['project_id']);
            $table->dropColumn('project_id');
            // Revert project_name change if needed, but safe to leave nullable
        });
    }
};
