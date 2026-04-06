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
        Schema::create('attendance_regularizations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->date('date'); // The day being corrected
            
            // What was wrong?
            $table->string('reason'); // "Forgot Punch", "System Issue", "Work Outside"
            
            // Proposed Correction
            $table->time('regularized_in_time')->nullable();
            $table->time('regularized_out_time')->nullable();
            
            // Workflow
            $table->string('status')->default('Pending'); // Pending, Approved, Rejected
            $table->foreignId('approver_id')->nullable()->constrained('users');
            $table->text('approver_remarks')->nullable();
            
            $table->timestamps();
            
            // One pending request per day to avoid spam
            $table->unique(['employee_id', 'date', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_regularizations');
    }
};
