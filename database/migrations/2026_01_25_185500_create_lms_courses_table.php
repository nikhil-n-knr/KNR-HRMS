<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lms_courses', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('category')->nullable();
            $table->string('thumbnail_path')->nullable();
            
            // Compliance Settings
            $table->integer('validity_days')->default(365);
            $table->string('target_audience_type')->default('all'); // all, department, gender, role, location
            $table->json('target_audience_config')->nullable(); // {"department_ids": [1,2]}
            $table->integer('deadline_days_from_joining')->nullable(); // 30 days from DOJ
            
            // Assessment Config
            $table->decimal('passing_score', 5, 2)->default(80.00);
            $table->integer('max_attempts')->default(3);
            $table->integer('timer_minutes')->nullable(); // NULL = no timer
            $table->boolean('shuffle_questions')->default(true);
            $table->boolean('shuffle_options')->default(true);
            $table->integer('question_pool_size')->nullable(); // Pick 10 from 50
            
            // Certificate Settings
            $table->boolean('auto_generate_certificate')->default(true);
            $table->string('certificate_template_path')->nullable();
            
            // Restrictions
            $table->boolean('disable_seeking')->default(false); // For videos
            $table->integer('min_time_per_section')->nullable(); // Seconds
            $table->boolean('prevent_copy_paste')->default(true);
            $table->boolean('track_tab_switches')->default(true);
            $table->integer('max_tab_switches')->default(3);
            
            // Failure Actions
            $table->enum('action_on_fail', ['lock', 'cooloff', 'notify'])->default('cooloff');
            $table->integer('cooloff_hours')->default(24);
            
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lms_courses');
    }
};
