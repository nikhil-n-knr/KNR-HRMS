<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deployment_rounds', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->foreignId('project_id')->constrained()->cascadeOnDelete();
            $blueprint->string('version')->nullable();
            $blueprint->string('status')->default('planning'); // planning, staging, production
            $blueprint->text('notes')->nullable();
            $blueprint->timestamp('deployed_at')->nullable();
            $blueprint->timestamps();
        });

        Schema::table('bug_tickets', function (Blueprint $table) {
            $table->foreignId('deployment_round_id')->nullable()->constrained('deployment_rounds')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('bug_tickets', function (Blueprint $table) {
            $table->dropConstrainedForeignId('deployment_round_id');
        });
        Schema::dropIfExists('deployment_rounds');
    }
};
