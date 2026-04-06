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
        Schema::create('bug_assignees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bug_ticket_id')->constrained()->cascadeOnDelete();
            $table->morphs('assignee'); // creates assignee_id and assignee_type
            $table->timestamps();

            $table->unique(['bug_ticket_id', 'assignee_id', 'assignee_type'], 'bug_assignee_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bug_assignees');
    }
};
