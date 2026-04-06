<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('lms_attempts')) {
            Schema::create('lms_attempts', function (Blueprint $table) {
                $table->id();
                $table->string('session_id')->nullable(); // For anon/guest tracking if needed
                $table->foreignId('assignment_id')->constrained('lms_assignments')->onDelete('cascade');
                $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
                $table->foreignId('course_id')->constrained('lms_courses')->onDelete('cascade');
                
                $table->integer('attempt_number');
                $table->timestamp('started_at');
                $table->timestamp('submitted_at')->nullable();
                $table->integer('time_spent_seconds')->nullable();
                
                $table->decimal('score_obtained', 5, 2)->nullable();
                $table->decimal('max_score', 5, 2)->nullable();
                $table->decimal('percentage', 5, 2)->nullable();
                $table->boolean('is_passed')->default(false);
                
                $table->json('answers_log'); // Store user answers {question_id: answer_id}
                $table->integer('tab_switches_count')->default(0);
                $table->json('violations')->nullable(); // Store detailed violation logs
                
                $table->enum('status', ['in_progress', 'submitted', 'abandoned'])->default('in_progress');
                
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('lms_attempts');
    }
};
