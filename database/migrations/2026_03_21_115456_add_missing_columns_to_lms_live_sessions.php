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
        Schema::table('lms_live_sessions', function (Blueprint $table) {
            if (!Schema::hasColumn('lms_live_sessions', 'activity_id')) {
                $table->unsignedBigInteger('activity_id')->nullable()->after('id');
            }
            if (!Schema::hasColumn('lms_live_sessions', 'module_id')) {
                $table->unsignedBigInteger('module_id')->nullable()->after('course_id');
            }
            if (!Schema::hasColumn('lms_live_sessions', 'agenda')) {
                $table->text('agenda')->nullable()->after('title');
            }
            if (!Schema::hasColumn('lms_live_sessions', 'co_host_ids')) {
                $table->json('co_host_ids')->nullable()->after('host_user_id');
            }
            if (!Schema::hasColumn('lms_live_sessions', 'duration_minutes')) {
                $table->integer('duration_minutes')->default(60)->after('scheduled_at');
            }
            if (!Schema::hasColumn('lms_live_sessions', 'provider')) {
                $table->string('provider')->default('zoom')->after('duration_minutes');
            }
            if (!Schema::hasColumn('lms_live_sessions', 'meeting_url')) {
                $table->string('meeting_url')->nullable()->after('provider');
            }
            if (!Schema::hasColumn('lms_live_sessions', 'meeting_id')) {
                $table->string('meeting_id')->nullable()->after('meeting_url');
            }
            if (!Schema::hasColumn('lms_live_sessions', 'meeting_password')) {
                $table->string('meeting_password')->nullable()->after('meeting_id');
            }
            if (!Schema::hasColumn('lms_live_sessions', 'provider_config')) {
                $table->json('provider_config')->nullable()->after('meeting_password');
            }
            if (!Schema::hasColumn('lms_live_sessions', 'min_attendance_pct')) {
                $table->integer('min_attendance_pct')->default(0)->after('provider_config');
            }
            if (!Schema::hasColumn('lms_live_sessions', 'auto_record')) {
                $table->boolean('auto_record')->default(false)->after('min_attendance_pct');
            }
            if (!Schema::hasColumn('lms_live_sessions', 'recording_url')) {
                $table->string('recording_url')->nullable()->after('auto_record');
            }
            if (!Schema::hasColumn('lms_live_sessions', 'status')) {
                $table->string('status', 20)->default('scheduled')->after('recording_url');
            }
            if (!Schema::hasColumn('lms_live_sessions', 'started_at')) {
                $table->timestamp('started_at')->nullable()->after('status');
            }
            if (!Schema::hasColumn('lms_live_sessions', 'ended_at')) {
                $table->timestamp('ended_at')->nullable()->after('started_at');
            }
            if (!Schema::hasColumn('lms_live_sessions', 'actual_duration_minutes')) {
                $table->integer('actual_duration_minutes')->nullable()->after('ended_at');
            }
            if (!Schema::hasColumn('lms_live_sessions', 'peak_attendees')) {
                $table->integer('peak_attendees')->default(0)->after('actual_duration_minutes');
            }
            if (!Schema::hasColumn('lms_live_sessions', 'settings')) {
                $table->json('settings')->nullable()->after('peak_attendees');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lms_live_sessions', function (Blueprint $table) {
            $table->dropColumn([
                'activity_id', 'module_id', 'agenda', 'co_host_ids', 'duration_minutes',
                'provider', 'meeting_url', 'meeting_id', 'meeting_password', 'provider_config',
                'min_attendance_pct', 'auto_record', 'recording_url', 'status',
                'started_at', 'ended_at', 'actual_duration_minutes', 'peak_attendees', 'settings'
            ]);
        });
    }
};
