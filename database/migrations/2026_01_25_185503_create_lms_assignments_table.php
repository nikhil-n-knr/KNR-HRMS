<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('lms_assignments')) {
            Schema::create('lms_assignments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('course_id')->constrained('lms_courses')->cascadeOnDelete();
                $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
                $table->date('assigned_on');
                $table->date('due_date');
                $table->date('valid_until')->nullable(); // Expiry date for retake
                $table->enum('status', ['pending', 'in_progress', 'completed', 'overdue', 'expired'])->default('pending');
                $table->foreignId('assigned_by')->constrained('users');
                $table->timestamp('completed_at')->nullable();
                $table->timestamps();
                
                $table->unique(['course_id', 'employee_id', 'assigned_on']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('lms_assignments');
    }
};
