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
        Schema::dropIfExists('project_stage_assignees');

        Schema::create('project_stage_assignees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_stage_id')->constrained('project_stages')->onDelete('cascade');
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade'); // Changed to employee_id
            $table->boolean('notify_on_entry')->default(true);
            $table->timestamps();
            
            $table->unique(['project_stage_id', 'employee_id']); // Prevent duplicates
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_stage_assignees');
    }
};
