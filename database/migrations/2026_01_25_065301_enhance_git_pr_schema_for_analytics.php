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
        Schema::table('git_pull_requests', function (Blueprint $table) {
            $table->integer('additions')->default(0)->after('closed_at');
            $table->integer('deletions')->default(0)->after('additions');
        });

        Schema::create('git_pr_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('git_pull_request_id')->constrained()->cascadeOnDelete();
            
            // External Identity
            $table->string('external_id')->nullable()->comment('Review ID from provider');
            $table->string('reviewer_name');
            $table->string('reviewer_username')->nullable();
            
            // Linkage to Employee (Optional, filled by linker)
            $table->foreignId('employee_id')->nullable()->constrained('employees')->nullOnDelete();
            
            $table->string('state')->comment('APPROVED, CHANGES_REQUESTED, COMMENTED, DISMISSED, PENDING');
            $table->timestamp('submitted_at')->nullable();
            $table->integer('comments_count')->default(0); // For Depth Score
            
            $table->timestamps();
            
            // A reviewer can have multiple reviews per PR if they review multiple times? 
            // Or usually we track the "Latest State".  Let's allow multiple for history if needed, 
            // but for "Load Balancer" we might query latest. Most providers give a list of reviews.
            // Let's index for quick lookup.
            $table->index(['git_pull_request_id', 'state']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('git_pr_reviews');

        Schema::table('git_pull_requests', function (Blueprint $table) {
            $table->dropColumn(['additions', 'deletions']);
        });
    }
};
