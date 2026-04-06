<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lms_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('lms_courses')->cascadeOnDelete();
            $table->foreignId('content_id')->nullable()->constrained('lms_course_content')->nullOnDelete();
            $table->enum('type', ['mcq', 'multi_select', 'text', 'scenario', 'image_based']);
            $table->text('question_text');
            $table->string('image_path')->nullable(); // For image-based questions
            $table->text('scenario_context')->nullable(); // For scenario-based
            $table->json('options'); // [{"id": "a", "text": "...", "is_correct": true, "score": 10}]
            $table->integer('score_weight')->default(1); // Some questions worth more
            $table->integer('max_score')->default(10);
            $table->text('explanation')->nullable(); // Shown after answering
            $table->boolean('require_manual_grading')->default(false); // For text questions
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lms_questions');
    }
};
