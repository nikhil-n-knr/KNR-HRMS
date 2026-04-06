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
        Schema::table('interview_feedback', function (Blueprint $table) {
            if (!Schema::hasColumn('interview_feedback', 'recording_url')) {
                $table->string('recording_url')->nullable()->after('attachment_path');
            }
        });

        Schema::table('job_applications', function (Blueprint $table) {
            if (!Schema::hasColumn('job_applications', 'screening_recording_url')) {
                $table->string('screening_recording_url')->nullable()->after('status');
            }
            if (!Schema::hasColumn('job_applications', 'screening_attachment_path')) {
                $table->string('screening_attachment_path')->nullable()->after('screening_recording_url');
            }
             if (!Schema::hasColumn('job_applications', 'screening_rating')) {
                $table->tinyInteger('screening_rating')->nullable()->after('screening_attachment_path');
            }
             if (!Schema::hasColumn('job_applications', 'screening_feedback')) {
                $table->text('screening_feedback')->nullable()->after('screening_rating');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('interview_feedback', function (Blueprint $table) {
            $table->dropColumn('recording_url');
        });
        
        Schema::table('job_applications', function (Blueprint $table) {
            $table->dropColumn([
                'screening_recording_url', 
                'screening_attachment_path',
                'screening_rating',
                'screening_feedback'
            ]);
        });
    }
};
