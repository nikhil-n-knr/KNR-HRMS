<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lms_course_content', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('lms_courses')->cascadeOnDelete();
            $table->integer('order')->default(0);
            $table->enum('type', ['video', 'pdf', 'text', 'assessment']);
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('file_path')->nullable(); // For video/pdf
            $table->text('content')->nullable(); // For text lessons
            $table->integer('min_time_seconds')->nullable(); // Minimum time to spend
            $table->boolean('is_mandatory')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lms_course_content');
    }
};
