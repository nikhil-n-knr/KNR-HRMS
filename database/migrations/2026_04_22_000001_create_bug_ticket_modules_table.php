<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bug_ticket_modules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bug_ticket_id')->constrained('bug_tickets')->onDelete('cascade');
            $table->foreignId('project_module_id')->constrained('project_modules')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['bug_ticket_id', 'project_module_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bug_ticket_modules');
    }
};
