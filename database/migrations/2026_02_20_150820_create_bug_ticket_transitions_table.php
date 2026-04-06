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
        Schema::create('bug_ticket_transitions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bug_ticket_id')->constrained('bug_tickets')->onDelete('cascade');
            $table->foreignId('from_stage_id')->nullable()->constrained('workflow_stages')->onDelete('set null');
            $table->foreignId('to_stage_id')->constrained('workflow_stages')->onDelete('cascade');
            
            // Polymorphic changed_by
            $table->unsignedBigInteger('user_id')->nullable(); // Who changed it
            
            $table->timestamps();
            
            $table->index(['bug_ticket_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bug_ticket_transitions');
    }
};
