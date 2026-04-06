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
            if (!Schema::hasColumn('project_tasks', 'start_date')) {
                $table->date('start_date')->nullable()->after('priority');
            }
            if (!Schema::hasColumn('project_tasks', 'version')) {
                $table->integer('version')->default(1)->after('updated_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_tasks', function (Blueprint $table) {
            $table->dropColumn(['start_date', 'version']);
        });
    }
};
