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
        Schema::create('ai_analysis_logs', function (Blueprint $table) {
            $table->id();
            $table->string('analyzable_type'); // Timesheet, AttendanceLog, LeaveRequest
            $table->unsignedBigInteger('analyzable_id');
            
            $table->string('type'); // 'Anomaly', 'Prediction', 'Nudge'
            $table->string('severity')->default('Info'); // Info, Warning, Critical
            $table->float('confidence_score')->default(0.0); // 0.0 to 1.0
            
            $table->json('analysis_data')->nullable(); // detailed reasons, vectors, etc
            $table->text('summary')->nullable(); // Human readable summary "Worked 18 hours"
            
            $table->boolean('is_reviewed')->default(false); // Did a human check this?
            
            $table->timestamps();
            
            $table->index(['analyzable_type', 'analyzable_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_analysis_logs');
    }
};
