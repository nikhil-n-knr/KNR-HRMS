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
        Schema::create('bug_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bug_ticket_id')->constrained('bug_tickets')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // Actor
            $table->string('activity_type'); // e.g., 'created', 'updated', 'commented', 'status_changed'
            $table->text('description')->nullable(); // Human readable
            $table->json('details')->nullable(); // Old/New values
            $table->timestamps();
            
            $table->index(['bug_ticket_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bug_activities');
    }
};
