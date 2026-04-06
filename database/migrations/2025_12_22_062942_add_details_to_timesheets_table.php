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
        Schema::table('timesheets', function (Blueprint $table) {
            $table->string('task_type')->nullable()->after('task_description'); // e.g. Development, Meeting, Testing
            $table->boolean('is_billable')->default(true)->after('hours_spent');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('timesheets', function (Blueprint $table) {
            $table->dropColumn(['task_type', 'is_billable']);
        });
    }
};
