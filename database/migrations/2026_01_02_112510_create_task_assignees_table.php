<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('task_assignees');

        Schema::create('task_assignees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained('project_tasks')->onDelete('cascade');
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->timestamps();
            
            $table->unique(['task_id', 'employee_id']);
        });

        // Migrate existing data (mapping legacy assignee_id which WAS employee_id)
        if (Schema::hasColumn('project_tasks', 'assignee_id')) {
            $tasks = DB::table('project_tasks')->whereNotNull('assignee_id')->get();
            foreach ($tasks as $task) {
                // Verify employee exists
                if (DB::table('employees')->where('id', $task->assignee_id)->exists()) {
                     DB::table('task_assignees')->insertOrIgnore([
                        'task_id' => $task->id,
                        'employee_id' => $task->assignee_id,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_assignees');
    }
};
