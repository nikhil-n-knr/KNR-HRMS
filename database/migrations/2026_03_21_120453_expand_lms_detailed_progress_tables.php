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
        Schema::table('lms_concept_progress', function (Blueprint $table) {
            if (!Schema::hasColumn('lms_concept_progress', 'chapter_id')) {
                $table->unsignedBigInteger('chapter_id')->nullable()->after('concept_id');
            }
            if (!Schema::hasColumn('lms_concept_progress', 'module_id')) {
                $table->unsignedBigInteger('module_id')->nullable()->after('chapter_id');
            }
            if (!Schema::hasColumn('lms_concept_progress', 'course_id')) {
                $table->unsignedBigInteger('course_id')->nullable()->after('module_id');
            }
            if (!Schema::hasColumn('lms_concept_progress', 'status')) {
                $table->string('status', 20)->default('pending')->after('user_id');
            }
            if (!Schema::hasColumn('lms_concept_progress', 'completion_pct')) {
                $table->decimal('completion_pct', 5, 2)->default(0.00)->after('status');
            }
            if (!Schema::hasColumn('lms_concept_progress', 'time_spent_seconds')) {
                $table->unsignedBigInteger('time_spent_seconds')->default(0)->after('completion_pct');
            }
            if (!Schema::hasColumn('lms_concept_progress', 'video_watch_seconds')) {
                $table->unsignedBigInteger('video_watch_seconds')->default(0)->after('time_spent_seconds');
            }
            if (!Schema::hasColumn('lms_concept_progress', 'video_completed')) {
                $table->boolean('video_completed')->default(false)->after('video_watch_seconds');
            }
            if (!Schema::hasColumn('lms_concept_progress', 'reading_completed')) {
                $table->boolean('reading_completed')->default(false)->after('video_completed');
            }
            if (!Schema::hasColumn('lms_concept_progress', 'quiz_passed')) {
                $table->boolean('quiz_passed')->default(false)->after('reading_completed');
            }
            if (!Schema::hasColumn('lms_concept_progress', 'quiz_best_score')) {
                $table->decimal('quiz_best_score', 5, 2)->default(0.00)->after('quiz_passed');
            }
            if (!Schema::hasColumn('lms_concept_progress', 'assignment_submitted')) {
                $table->boolean('assignment_submitted')->default(false)->after('quiz_best_score');
            }
            if (!Schema::hasColumn('lms_concept_progress', 'assignment_approved')) {
                $table->boolean('assignment_approved')->default(false)->after('assignment_submitted');
            }
            if (!Schema::hasColumn('lms_concept_progress', 'completed_at')) {
                $table->timestamp('completed_at')->nullable()->after('is_completed');
            }
            if (!Schema::hasColumn('lms_concept_progress', 'last_activity_at')) {
                $table->timestamp('last_activity_at')->nullable()->after('completed_at');
            }
        });

        Schema::table('lms_module_progress', function (Blueprint $table) {
            if (!Schema::hasColumn('lms_module_progress', 'course_id')) {
                $table->unsignedBigInteger('course_id')->nullable()->after('module_id');
            }
            if (!Schema::hasColumn('lms_module_progress', 'completion_pct')) {
                $table->decimal('completion_pct', 5, 2)->default(0.00)->after('user_id');
            }
            if (!Schema::hasColumn('lms_module_progress', 'concepts_total')) {
                $table->integer('concepts_total')->default(0)->after('completion_pct');
            }
            if (!Schema::hasColumn('lms_module_progress', 'concepts_completed')) {
                $table->integer('concepts_completed')->default(0)->after('concepts_total');
            }
            if (!Schema::hasColumn('lms_module_progress', 'total_time_seconds')) {
                $table->unsignedBigInteger('total_time_seconds')->default(0)->after('concepts_completed');
            }
            if (!Schema::hasColumn('lms_module_progress', 'avg_quiz_score')) {
                $table->decimal('avg_quiz_score', 5, 2)->default(0.00)->after('total_time_seconds');
            }
            if (!Schema::hasColumn('lms_module_progress', 'certificate_issued')) {
                $table->boolean('certificate_issued')->default(false)->after('is_completed');
            }
            if (!Schema::hasColumn('lms_module_progress', 'completed_at')) {
                $table->timestamp('completed_at')->nullable()->after('certificate_issued');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lms_concept_progress', function (Blueprint $table) {
            $table->dropColumn([
                'chapter_id', 'module_id', 'course_id', 'status', 'completion_pct',
                'time_spent_seconds', 'video_watch_seconds', 'video_completed',
                'reading_completed', 'quiz_passed', 'quiz_best_score',
                'assignment_submitted', 'assignment_approved',
                'completed_at', 'last_activity_at'
            ]);
        });
        Schema::table('lms_module_progress', function (Blueprint $table) {
            $table->dropColumn([
                'course_id', 'completion_pct', 'concepts_total', 'concepts_completed',
                'total_time_seconds', 'avg_quiz_score', 'certificate_issued', 'completed_at'
            ]);
        });
    }
};
