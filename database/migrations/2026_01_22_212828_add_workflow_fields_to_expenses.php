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
        Schema::table('expenses', function (Blueprint $table) {
            if (!Schema::hasColumn('expenses', 'expense_category_id')) {
                $table->foreignId('expense_category_id')->nullable()->constrained('expense_categories');
            }
            if (!Schema::hasColumn('expenses', 'project_id')) {
                $table->foreignId('project_id')->nullable()->constrained('projects');
                $table->foreignId('client_id')->nullable()->constrained('clients');
                $table->boolean('is_billable')->default(false);
            }
            if (!Schema::hasColumn('expenses', 'current_stage_id')) {
                $table->foreignId('current_stage_id')->nullable()->constrained('workflow_stages');
                $table->text('rejection_reason')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            //
        });
    }
};
