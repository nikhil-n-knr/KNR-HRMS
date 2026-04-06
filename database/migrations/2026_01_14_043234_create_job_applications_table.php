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
        Schema::create('job_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('candidate_id')->constrained('candidates')->cascadeOnDelete();
            $table->foreignId('job_posting_id')->constrained('job_postings')->cascadeOnDelete();
            
            $table->string('status')->default('Applied'); // Applied, Screening, Interview, Offer, Hired, Rejected
            $table->string('stage')->nullable(); // For Kanban columns
            $table->text('cover_letter')->nullable(); 
            $table->decimal('score', 5, 2)->nullable(); // 0-100
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_applications');
    }
};
