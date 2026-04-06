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
        Schema::table('project_tasks', function (Blueprint $table) {
            $table->index('status');
            $table->index('created_by');
            $table->index('project_id'); // Ensure this exists
        });
        

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_tasks', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['created_by']);
            $table->dropIndex(['project_id']);
        });
        
        Schema::table('work_assignments', function (Blueprint $table) {
             $table->dropIndex(['task_id', 'assignee_id']);
        });
    }
};
