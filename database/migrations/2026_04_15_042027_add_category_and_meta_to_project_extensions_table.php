<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_extensions', function (Blueprint $table) {
            // Semantic category: the WHY behind the extension
            // priority_conflict : Person was on other work, time was lost
            // scope_change      : New features / wrong initial estimation  
            // complexity_drag   : Work is harder than expected, execution slower
            $table->string('category')->nullable()->after('type')
                  ->comment('priority_conflict | scope_change | complexity_drag');

            // Rich context metadata per category (JSON blob)
            $table->json('extension_meta')->nullable()->after('extended_end_date')
                  ->comment('Category-specific fields: planned_hours, actual_hours, deficit_hours, resources_added, etc.');
        });
    }

    public function down(): void
    {
        Schema::table('project_extensions', function (Blueprint $table) {
            $table->dropColumn(['category', 'extension_meta']);
        });
    }
};
