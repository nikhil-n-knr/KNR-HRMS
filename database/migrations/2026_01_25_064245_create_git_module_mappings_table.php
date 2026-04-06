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
        Schema::create('git_module_mappings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('git_repository_id')->constrained()->cascadeOnDelete();
            // Assuming 'project_modules' is the table for ProjectModule (checked in analysis)
            $table->foreignId('project_module_id')->constrained('project_modules')->cascadeOnDelete();
            
            $table->string('path_pattern')->comment('e.g. /app/Services/ or regex');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('git_module_mappings');
    }
};
