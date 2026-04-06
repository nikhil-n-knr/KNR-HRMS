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
        Schema::table('lms_course_progress', function (Blueprint $table) {
            if (!Schema::hasColumn('lms_course_progress', 'completion_pct')) {
                $table->decimal('completion_pct', 5, 2)->default(0.00)->after('user_id');
            }
            if (!Schema::hasColumn('lms_course_progress', 'modules_total')) {
                $table->integer('modules_total')->default(0)->after('completion_pct');
            }
            if (!Schema::hasColumn('lms_course_progress', 'modules_completed')) {
                $table->integer('modules_completed')->default(0)->after('modules_total');
            }
            if (!Schema::hasColumn('lms_course_progress', 'concepts_total')) {
                $table->integer('concepts_total')->default(0)->after('modules_completed');
            }
            if (!Schema::hasColumn('lms_course_progress', 'concepts_completed')) {
                $table->integer('concepts_completed')->default(0)->after('concepts_total');
            }
            if (!Schema::hasColumn('lms_course_progress', 'total_watch_seconds')) {
                $table->unsignedBigInteger('total_watch_seconds')->default(0)->after('concepts_completed');
            }
            if (!Schema::hasColumn('lms_course_progress', 'total_time_seconds')) {
                $table->unsignedBigInteger('total_time_seconds')->default(0)->after('total_watch_seconds');
            }
            if (!Schema::hasColumn('lms_course_progress', 'avg_quiz_score')) {
                $table->decimal('avg_quiz_score', 5, 2)->default(0.00)->after('total_time_seconds');
            }
            if (!Schema::hasColumn('lms_course_progress', 'assignments_submitted')) {
                $table->integer('assignments_submitted')->default(0)->after('avg_quiz_score');
            }
            if (!Schema::hasColumn('lms_course_progress', 'live_sessions_attended')) {
                $table->integer('live_sessions_attended')->default(0)->after('assignments_submitted');
            }
            if (!Schema::hasColumn('lms_course_progress', 'certificate_issued')) {
                $table->boolean('certificate_issued')->default(false)->after('is_completed');
            }
            if (!Schema::hasColumn('lms_course_progress', 'completed_at')) {
                $table->timestamp('completed_at')->nullable()->after('certificate_issued');
            }
            if (!Schema::hasColumn('lms_course_progress', 'last_activity_at')) {
                $table->timestamp('last_activity_at')->nullable()->after('completed_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lms_course_progress', function (Blueprint $table) {
            $table->dropColumn([
                'completion_pct', 'modules_total', 'modules_completed',
                'concepts_total', 'concepts_completed',
                'total_watch_seconds', 'total_time_seconds',
                'avg_quiz_score', 'assignments_submitted', 'live_sessions_attended',
                'certificate_issued', 'completed_at', 'last_activity_at'
            ]);
        });
    }
};
