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
        Schema::table('project_tasks', function (Blueprint $table) {
            $table->foreignId('sprint_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('stage_id')->nullable()->constrained('project_stages')->nullOnDelete();
            $table->string('git_branch_url')->nullable();
            $table->string('git_pr_url')->nullable();
            $table->text('qa_notes')->nullable();
            $table->string('deployed_to')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_tasks', function (Blueprint $table) {
            //
        });
    }
};
