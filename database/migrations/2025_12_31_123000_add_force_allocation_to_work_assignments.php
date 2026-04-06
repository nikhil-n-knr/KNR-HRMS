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
        Schema::table('work_assignments', function (Blueprint $table) {
            if (!Schema::hasColumn('work_assignments', 'force_allocation')) {
                $table->boolean('force_allocation')->default(false)->after('end_date');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('work_assignments', function (Blueprint $table) {
            if (Schema::hasColumn('work_assignments', 'force_allocation')) {
                $table->dropColumn('force_allocation');
            }
        });
    }
};
