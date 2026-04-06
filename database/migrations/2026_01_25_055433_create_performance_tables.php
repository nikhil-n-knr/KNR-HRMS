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
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('goal_ratings');
        Schema::dropIfExists('appraisals');
        Schema::dropIfExists('goals');
        Schema::dropIfExists('appraisal_cycles');
        Schema::enableForeignKeyConstraints();

        // 1. Appraisal Cycles (e.g., Annual 2025, Q1 2026)
        Schema::create('appraisal_cycles', function (Blueprint $table) {
            $table->id();
            $table->string('name'); 
            $table->date('start_date');
            $table->date('end_date');
            $table->date('self_review_deadline')->nullable();
            
            $table->enum('status', ['Setup', 'Active', 'Locked', 'Completed'])->default('Setup');
            $table->boolean('is_active')->default(false); // Only one active at a time usually
            
            $table->timestamps();
            $table->softDeletes();
        });

        // 2. Goals (KRAs / KPIs)
        Schema::create('goals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('appraisal_cycle_id')->constrained('appraisal_cycles')->cascadeOnDelete();
            
            $table->string('title');
            $table->text('description')->nullable();
            $table->integer('weightage')->default(0); // e.g. 20%
            
            $table->enum('status', ['Draft', 'Pending Approval', 'Approved', 'Rejected'])->default('Draft');
            $table->text('manager_remarks')->nullable();
            
            // Progress Tracking
            $table->integer('progress')->default(0); // 0-100%
            $table->enum('progress_status', ['Not Started', 'In Progress', 'Completed', 'Deferred'])->default('Not Started');
            
            $table->timestamps();
            $table->softDeletes();
        });

        // 3. Appraisals (The Main Review Record)
        Schema::create('appraisals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('appraisal_cycle_id')->constrained('appraisal_cycles')->cascadeOnDelete();
            
            // Workflow State
            // 'Self Review', 'Manager Review', 'HR Review', 'Closed'
            $table->string('stage')->default('Self Review'); 
            
            // Ratings (1-5 Scale usually)
            $table->decimal('self_rating', 3, 2)->nullable();
            $table->decimal('manager_rating', 3, 2)->nullable();
            $table->decimal('final_rating', 3, 2)->nullable();
            
            // Comments
            $table->text('self_comments')->nullable();
            $table->text('manager_comments')->nullable();
            $table->text('hr_comments')->nullable();
            
            $table->date('submitted_at')->nullable();
            $table->date('reviewed_at')->nullable();
            
            $table->timestamps();
            $table->unique(['employee_id', 'appraisal_cycle_id']);
        });
        
        // 4. Goal Ratings (Per Goal Rating in Appraisal)
        Schema::create('goal_ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appraisal_id')->constrained('appraisals')->cascadeOnDelete();
            $table->foreignId('goal_id')->constrained('goals')->cascadeOnDelete();
            
            $table->decimal('self_rating', 3, 2)->nullable();
            $table->text('self_remarks')->nullable();
            
            $table->decimal('manager_rating', 3, 2)->nullable();
            $table->text('manager_remarks')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('goal_ratings');
        Schema::dropIfExists('appraisals');
        Schema::dropIfExists('goals');
        Schema::dropIfExists('appraisal_cycles');
    }
};
