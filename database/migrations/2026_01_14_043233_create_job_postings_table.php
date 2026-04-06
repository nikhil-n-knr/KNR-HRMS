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
        Schema::create('job_postings', function (Blueprint $table) {
            $table->id();
            $table->string('job_code')->unique(); // ENG-2024-001
            $table->string('title');
            $table->text('description')->nullable();
            
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->foreignId('location_id')->nullable()->constrained('locations')->nullOnDelete();
            
            $table->foreignId('job_category_id')->nullable()->constrained('job_categories')->nullOnDelete();
            
            $table->string('type')->default('Full-time'); // Full-time, Contract
            $table->string('experience_level')->nullable(); // Junior, Senior
            $table->date('valid_through')->nullable();
            
            $table->string('status')->default('Draft'); // Draft, Published, Closed
            
            // Smart Features
            $table->json('notification_config')->nullable(); // { loops: [], interviewers: [] }
            $table->json('required_documents')->nullable(); // ["Resume", "Portfolio"]
            
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_postings');
    }
};
