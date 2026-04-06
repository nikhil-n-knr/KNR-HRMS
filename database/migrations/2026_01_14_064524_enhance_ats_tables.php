<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        // 1. Create Screening Templates (Process only if not exists)
        if (!Schema::hasTable('screening_templates')) {
            Schema::create('screening_templates', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->json('questions'); // Schema: [{id, type, label, options, validation}]
                $table->foreignId('created_by')->constrained('users');
                $table->timestamps();
            });
        }

        // 2. Update Job Postings
        Schema::table('job_postings', function (Blueprint $table) {
            if (!Schema::hasColumn('job_postings', 'screening_template_id')) {
                $table->foreignId('screening_template_id')->nullable()->constrained('screening_templates')->nullOnDelete();
            }
        });

        // 3. Update Candidates
        Schema::table('candidates', function (Blueprint $table) {
            if (!Schema::hasColumn('candidates', 'skills')) {
                $table->json('skills')->nullable();
            }
            if (!Schema::hasColumn('candidates', 'resume_text')) {
                $table->longText('resume_text')->nullable();
            }
            if (!Schema::hasColumn('candidates', 'portfolio_url')) {
                $table->string('portfolio_url')->nullable();
            }
        });

        // 4. Update Job Applications
        Schema::table('job_applications', function (Blueprint $table) {
            if (!Schema::hasColumn('job_applications', 'ai_score')) {
                $table->integer('ai_score')->nullable();
            }
            if (!Schema::hasColumn('job_applications', 'ai_analysis')) {
                $table->text('ai_analysis')->nullable();
            }
            if (!Schema::hasColumn('job_applications', 'answers')) {
                $table->json('answers')->nullable();
            }
            if (!Schema::hasColumn('job_applications', 'video_answers')) {
                $table->json('video_answers')->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('job_applications', function (Blueprint $table) {
            $table->dropColumn(['ai_score', 'ai_analysis', 'answers', 'video_answers']);
        });
        
        Schema::table('candidates', function (Blueprint $table) {
            $table->dropColumn(['skills', 'resume_text', 'portfolio_url']);
        });

        Schema::table('job_postings', function (Blueprint $table) {
            if (Schema::hasColumn('job_postings', 'screening_template_id')) {
                $table->dropForeign(['screening_template_id']);
                $table->dropColumn('screening_template_id');
            }
        });

        Schema::dropIfExists('screening_templates');
    }
};
