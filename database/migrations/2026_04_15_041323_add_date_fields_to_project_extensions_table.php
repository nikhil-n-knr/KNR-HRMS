<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_extensions', function (Blueprint $table) {
            $table->date('original_start_date')->nullable()->after('task_id');
            $table->date('original_end_date')->nullable()->after('original_start_date');
            $table->date('extended_end_date')->nullable()->after('original_end_date');
        });
    }

    public function down(): void
    {
        Schema::table('project_extensions', function (Blueprint $table) {
            $table->dropColumn(['original_start_date', 'original_end_date', 'extended_end_date']);
        });
    }
};
