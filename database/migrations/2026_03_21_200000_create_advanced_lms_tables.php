<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ─────────────────────────────────────────────────────────────────
        // 1. INSTITUTIONAL HIERARCHY
        // ─────────────────────────────────────────────────────────────────
        if (!Schema::hasTable('lms_institutions')) {
            Schema::create('lms_institutions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('parent_id')->nullable()->constrained('lms_institutions')->nullOnDelete();
                $table->string('name');
                $table->string('code', 50)->unique();
                $table->enum('type', ['state','district','board','university','college','department'])->default('college');
                $table->string('logo_path')->nullable();
                $table->string('primary_color', 20)->nullable();
                $table->string('secondary_color', 20)->nullable();
                $table->string('website')->nullable();
                $table->string('contact_email')->nullable();
                $table->string('contact_phone')->nullable();
                $table->text('address')->nullable();
                $table->json('settings')->nullable();
                $table->boolean('is_active')->default(true);
                $table->integer('depth')->default(0);
                $table->string('path')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // 2. PROGRAMS
        if (!Schema::hasTable('lms_programs')) {
            Schema::create('lms_programs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('institution_id')->constrained('lms_institutions')->cascadeOnDelete();
                $table->string('name');
                $table->string('code', 50)->nullable();
                $table->text('description')->nullable();
                $table->integer('duration_semesters')->default(8);
                $table->string('degree_type')->nullable();
                $table->string('specialization')->nullable();
                $table->json('syllabus_config')->nullable();
                $table->json('certificate_rules')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // 3. CATEGORIES
        if (!Schema::hasTable('lms_categories')) {
            Schema::create('lms_categories', function (Blueprint $table) {
                $table->id();
                $table->foreignId('parent_id')->nullable()->constrained('lms_categories')->nullOnDelete();
                $table->string('name');
                $table->string('slug')->unique();
                $table->text('description')->nullable();
                $table->string('icon')->nullable();
                $table->string('color', 20)->nullable();
                $table->string('thumbnail_path')->nullable();
                $table->integer('depth')->default(0);
                $table->string('path')->nullable();
                $table->integer('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // 4. ENROLLMENTS (Moved Up)
        if (!Schema::hasTable('lms_enrollments')) {
            Schema::create('lms_enrollments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('course_id')->constrained('lms_courses')->cascadeOnDelete();
                $table->foreignId('program_id')->nullable()->constrained('lms_programs')->nullOnDelete();
                $table->foreignId('institution_id')->nullable()->constrained('lms_institutions')->nullOnDelete();
                $table->enum('source', ['manual','erp_sync','self','bulk_import','program'])->default('manual');
                $table->enum('status', ['active','completed','suspended','dropped','expired'])->default('active');
                $table->integer('semester')->nullable();
                $table->timestamp('enrolled_at')->useCurrent();
                $table->timestamp('expires_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->foreignId('enrolled_by')->nullable()->constrained('users')->nullOnDelete();
                $table->json('metadata')->nullable();
                $table->timestamps();
                $table->unique(['user_id','course_id']);
            });
        }

        // 5. COURSE TABLES
        if (!Schema::hasTable('lms_course_institutions')) {
            Schema::create('lms_course_institutions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('course_id')->constrained('lms_courses')->cascadeOnDelete();
                $table->foreignId('institution_id')->constrained('lms_institutions')->cascadeOnDelete();
                $table->boolean('is_inherited')->default(false);
                $table->boolean('allow_customization')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('lms_course_programs')) {
            Schema::create('lms_course_programs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('course_id')->constrained('lms_courses')->cascadeOnDelete();
                $table->foreignId('program_id')->constrained('lms_programs')->cascadeOnDelete();
                $table->integer('semester')->nullable();
                $table->boolean('is_mandatory')->default(true);
                $table->timestamps();
            });
        }

        // 6. MODULES
        if (!Schema::hasTable('lms_modules')) {
            Schema::create('lms_modules', function (Blueprint $table) {
                $table->id();
                $table->foreignId('course_id')->constrained('lms_courses')->cascadeOnDelete();
                $table->string('title');
                $table->text('description')->nullable();
                $table->integer('sort_order')->default(0);
                $table->boolean('is_mandatory')->default(true);
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // 7. CHAPTERS
        if (!Schema::hasTable('lms_chapters')) {
            Schema::create('lms_chapters', function (Blueprint $table) {
                $table->id();
                $table->foreignId('module_id')->constrained('lms_modules')->cascadeOnDelete();
                $table->foreignId('course_id')->constrained('lms_courses')->cascadeOnDelete();
                $table->string('title');
                $table->integer('sort_order')->default(0);
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // 8. CONCEPTS
        if (!Schema::hasTable('lms_concepts')) {
            Schema::create('lms_concepts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('chapter_id')->constrained('lms_chapters')->cascadeOnDelete();
                $table->foreignId('module_id')->constrained('lms_modules')->cascadeOnDelete();
                $table->foreignId('course_id')->constrained('lms_courses')->cascadeOnDelete();
                $table->string('title');
                $table->integer('sort_order')->default(0);
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // 9. ACTIVITIES
        if (!Schema::hasTable('lms_activities')) {
            Schema::create('lms_activities', function (Blueprint $table) {
                $table->id();
                $table->foreignId('concept_id')->constrained('lms_concepts')->cascadeOnDelete();
                $table->foreignId('chapter_id')->constrained('lms_chapters')->cascadeOnDelete();
                $table->foreignId('module_id')->constrained('lms_modules')->cascadeOnDelete();
                $table->foreignId('course_id')->constrained('lms_courses')->cascadeOnDelete();
                $table->enum('type', ['video','reading','quiz','assignment','discussion','live_session'])->default('video');
                $table->string('title');
                $table->integer('sort_order')->default(0);
                $table->string('activityable_type')->nullable();
                $table->unsignedBigInteger('activityable_id')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // 10. VIDEO LESSONS
        if (!Schema::hasTable('lms_video_lessons')) {
            Schema::create('lms_video_lessons', function (Blueprint $table) {
                $table->id();
                $table->foreignId('activity_id')->constrained('lms_activities')->cascadeOnDelete();
                $table->string('video_url')->nullable();
                $table->integer('duration_seconds')->default(0);
                $table->decimal('min_watch_pct', 5, 2)->default(90.00);
                $table->timestamps();
            });
        }

        // 11. VIDEO SESSIONS
        if (!Schema::hasTable('lms_video_sessions')) {
            Schema::create('lms_video_sessions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('video_lesson_id')->constrained('lms_video_lessons')->cascadeOnDelete();
                $table->foreignId('activity_id')->constrained('lms_activities')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('enrollment_id')->nullable()->constrained('lms_enrollments')->nullOnDelete();
                $table->integer('total_watch_seconds')->default(0);
                $table->boolean('is_completed')->default(false);
                $table->timestamps();
            });
        }

        // 12. READING MATERIALS
        if (!Schema::hasTable('lms_reading_materials')) {
            Schema::create('lms_reading_materials', function (Blueprint $table) {
                $table->id();
                $table->foreignId('activity_id')->constrained('lms_activities')->cascadeOnDelete();
                $table->longText('content')->nullable();
                $table->timestamps();
            });
        }

        // 13. QUESTION BANKS
        if (!Schema::hasTable('lms_question_banks')) {
            Schema::create('lms_question_banks', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->foreignId('institution_id')->nullable()->constrained('lms_institutions')->nullOnDelete();
                $table->foreignId('created_by')->constrained('users');
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // 14. QUESTIONS
        if (!Schema::hasTable('lms_questions_v2')) {
            Schema::create('lms_questions_v2', function (Blueprint $table) {
                $table->id();
                $table->foreignId('bank_id')->nullable()->constrained('lms_question_banks')->nullOnDelete();
                $table->text('question_text');
                $table->json('options')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // 15. QUIZ CONFIGS
        if (!Schema::hasTable('lms_quiz_configs')) {
            Schema::create('lms_quiz_configs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('activity_id')->constrained('lms_activities')->cascadeOnDelete();
                $table->string('title');
                $table->integer('duration_minutes')->nullable();
                $table->decimal('pass_mark_pct', 5, 2)->default(60.00);
                $table->timestamps();
            });
        }

        // 16. QUIZ ATTEMPTS
        if (!Schema::hasTable('lms_quiz_attempts')) {
            Schema::create('lms_quiz_attempts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('quiz_config_id')->constrained('lms_quiz_configs')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->decimal('score_obtained', 8, 2)->default(0);
                $table->boolean('is_passed')->default(false);
                $table->timestamp('started_at');
                $table->timestamp('submitted_at')->nullable();
                $table->timestamps();
            });
        }

        // 17. QUIZ RESPONSES
        if (!Schema::hasTable('lms_quiz_responses')) {
            Schema::create('lms_quiz_responses', function (Blueprint $table) {
                $table->id();
                $table->foreignId('attempt_id')->constrained('lms_quiz_attempts')->cascadeOnDelete();
                $table->foreignId('question_id')->constrained('lms_questions_v2')->cascadeOnDelete();
                $table->json('selected_answer')->nullable();
                $table->boolean('is_correct')->default(false);
                $table->timestamps();
            });
        }

        // 18. ASSIGNMENTS
        if (!Schema::hasTable('lms_assignments_v2')) {
            Schema::create('lms_assignments_v2', function (Blueprint $table) {
                $table->id();
                $table->foreignId('activity_id')->constrained('lms_activities')->cascadeOnDelete();
                $table->string('title');
                $table->longText('description');
                $table->timestamps();
            });
        }

        // 19. ASSIGNMENT SUBMISSIONS
        if (!Schema::hasTable('lms_assignment_submissions')) {
            Schema::create('lms_assignment_submissions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('assignment_id')->constrained('lms_assignments_v2')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->timestamp('submitted_at')->nullable();
                $table->decimal('score', 8, 2)->nullable();
                $table->timestamps();
            });
        }

        // 20. LIVE SESSIONS
        if (!Schema::hasTable('lms_live_sessions')) {
            Schema::create('lms_live_sessions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('course_id')->constrained('lms_courses')->cascadeOnDelete();
                $table->string('title');
                $table->foreignId('host_user_id')->constrained('users');
                $table->timestamp('scheduled_at');
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // 21. LIVE ATTENDANCE
        if (!Schema::hasTable('lms_live_attendance')) {
            Schema::create('lms_live_attendance', function (Blueprint $table) {
                $table->id();
                $table->foreignId('session_id')->constrained('lms_live_sessions')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->integer('total_minutes_present')->default(0);
                $table->timestamps();
            });
        }

        // 22. CONCEPT PROGRESS
        if (!Schema::hasTable('lms_concept_progress')) {
            Schema::create('lms_concept_progress', function (Blueprint $table) {
                $table->id();
                $table->foreignId('enrollment_id')->constrained('lms_enrollments')->cascadeOnDelete();
                $table->foreignId('concept_id')->constrained('lms_concepts')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->boolean('is_completed')->default(false);
                $table->timestamps();
            });
        }

        // 23. MODULE PROGRESS
        if (!Schema::hasTable('lms_module_progress')) {
            Schema::create('lms_module_progress', function (Blueprint $table) {
                $table->id();
                $table->foreignId('enrollment_id')->constrained('lms_enrollments')->cascadeOnDelete();
                $table->foreignId('module_id')->constrained('lms_modules')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->boolean('is_completed')->default(false);
                $table->timestamps();
            });
        }

        // 24. COURSE PROGRESS
        if (!Schema::hasTable('lms_course_progress')) {
            Schema::create('lms_course_progress', function (Blueprint $table) {
                $table->id();
                $table->foreignId('enrollment_id')->constrained('lms_enrollments')->cascadeOnDelete();
                $table->foreignId('course_id')->constrained('lms_courses')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->boolean('is_completed')->default(false);
                $table->timestamps();
            });
        }

        // 25. CERTIFICATE TEMPLATES
        if (!Schema::hasTable('lms_certificate_templates')) {
            Schema::create('lms_certificate_templates', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->longText('html_template');
                $table->foreignId('created_by')->constrained('users');
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // 26. CERTIFICATE RULES
        if (!Schema::hasTable('lms_certificate_rules')) {
            Schema::create('lms_certificate_rules', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->foreignId('template_id')->nullable()->constrained('lms_certificate_templates')->nullOnDelete();
                $table->decimal('min_completion_pct', 5, 2)->default(100);
                $table->timestamps();
            });
        }

        // 27. CERTIFICATES ISSUED
        if (!Schema::hasTable('lms_certificates_v2')) {
            Schema::create('lms_certificates_v2', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('course_id')->nullable()->constrained('lms_courses')->nullOnDelete();
                $table->string('unique_code', 64)->unique();
                $table->timestamp('issued_at')->useCurrent();
                $table->timestamps();
            });
        }

        // 28. FORUMS
        if (!Schema::hasTable('lms_forums')) {
            Schema::create('lms_forums', function (Blueprint $table) {
                $table->id();
                $table->foreignId('course_id')->constrained('lms_courses')->cascadeOnDelete();
                $table->string('title');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('lms_forum_posts')) {
            Schema::create('lms_forum_posts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('forum_id')->constrained('lms_forums')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->longText('content');
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('lms_forum_replies')) {
            Schema::create('lms_forum_replies', function (Blueprint $table) {
                $table->id();
                $table->foreignId('post_id')->constrained('lms_forum_posts')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->longText('content');
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // 29. ROLES
        if (!Schema::hasTable('lms_user_roles')) {
            Schema::create('lms_user_roles', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('institution_id')->nullable()->constrained('lms_institutions')->nullOnDelete();
                $table->enum('role', ['super_admin','institution_admin','instructor','learner'])->default('learner');
                $table->timestamps();
            });
        }

        // 30. POINTS
        if (!Schema::hasTable('lms_user_points')) {
            Schema::create('lms_user_points', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('action');
                $table->integer('points')->default(0);
                $table->timestamps();
            });
        }

        // 31. SYNC LOG
        if (!Schema::hasTable('lms_erp_sync_logs')) {
            Schema::create('lms_erp_sync_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('institution_id')->nullable()->constrained('lms_institutions')->nullOnDelete();
                $table->integer('records_processed')->default(0);
                $table->timestamps();
            });
        }
    }

    public function down(): void {}
};
