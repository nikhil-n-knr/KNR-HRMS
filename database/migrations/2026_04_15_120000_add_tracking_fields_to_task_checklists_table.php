<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('task_checklists', function (Blueprint $table) {
            if (!Schema::hasColumn('task_checklists', 'assigned_to')) {
                $table->foreignId('assigned_to')->nullable()->after('position')->constrained('users')->nullOnDelete();
            }

            if (!Schema::hasColumn('task_checklists', 'planned_minutes')) {
                $table->unsignedInteger('planned_minutes')->nullable()->after('assigned_to');
            }

            if (!Schema::hasColumn('task_checklists', 'actual_minutes')) {
                $table->unsignedInteger('actual_minutes')->nullable()->after('planned_minutes');
            }

            if (!Schema::hasColumn('task_checklists', 'work_date')) {
                $table->date('work_date')->nullable()->after('actual_minutes');
            }

            if (!Schema::hasColumn('task_checklists', 'started_at')) {
                $table->dateTime('started_at')->nullable()->after('work_date');
            }

            if (!Schema::hasColumn('task_checklists', 'completed_at')) {
                $table->dateTime('completed_at')->nullable()->after('started_at');
            }

            if (!Schema::hasColumn('task_checklists', 'completed_by')) {
                $table->foreignId('completed_by')->nullable()->after('completed_at')->constrained('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('task_checklists', function (Blueprint $table) {
            if (Schema::hasColumn('task_checklists', 'completed_by')) {
                $table->dropConstrainedForeignId('completed_by');
            }

            if (Schema::hasColumn('task_checklists', 'completed_at')) {
                $table->dropColumn('completed_at');
            }

            if (Schema::hasColumn('task_checklists', 'started_at')) {
                $table->dropColumn('started_at');
            }

            if (Schema::hasColumn('task_checklists', 'work_date')) {
                $table->dropColumn('work_date');
            }

            if (Schema::hasColumn('task_checklists', 'actual_minutes')) {
                $table->dropColumn('actual_minutes');
            }

            if (Schema::hasColumn('task_checklists', 'planned_minutes')) {
                $table->dropColumn('planned_minutes');
            }

            if (Schema::hasColumn('task_checklists', 'assigned_to')) {
                $table->dropConstrainedForeignId('assigned_to');
            }
        });
    }
};
