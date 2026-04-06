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
        Schema::table('timesheets', function (Blueprint $table) {
            $table->foreignId('task_id')->nullable()->constrained('project_tasks')->nullOnDelete();
            $table->string('task_title')->nullable()->comment('Snapshot of task title or custom title for Other');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('timesheets', function (Blueprint $table) {
            $table->dropForeign(['task_id']);
            $table->dropColumn(['task_id', 'task_title']);
        });
    }
};
