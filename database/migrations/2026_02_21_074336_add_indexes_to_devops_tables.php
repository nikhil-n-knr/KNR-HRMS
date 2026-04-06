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
        Schema::table('git_commits', function (Blueprint $table) {
            $table->index('committed_at');
        });
        
        Schema::table('git_pull_requests', function (Blueprint $table) {
            $table->index('state');
            $table->index('created_at_provider');
            $table->index('merged_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('git_commits', function (Blueprint $table) {
            $table->dropIndex(['committed_at']);
        });
        
        Schema::table('git_pull_requests', function (Blueprint $table) {
            $table->dropIndex(['state']);
            $table->dropIndex(['created_at_provider']);
            $table->dropIndex(['merged_at']);
        });
    }
};
