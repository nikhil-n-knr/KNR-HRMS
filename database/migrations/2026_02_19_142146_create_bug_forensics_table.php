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
        Schema::create('bug_forensics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bug_ticket_id')->constrained('bug_tickets')->onDelete('cascade');
            $table->longText('session_recording')->nullable(); // Stores compressed rrweb events
            $table->json('browser_metadata')->nullable(); // UserAgent, Viewport, Network Type
            $table->json('console_logs')->nullable(); // Captured console errors/logs
            $table->string('git_branch')->nullable();
            $table->string('git_latest_commit')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bug_forensics');
    }
};
