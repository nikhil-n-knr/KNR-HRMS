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
        Schema::table('employees', function (Blueprint $table) {
            if (!Schema::hasColumn('employees', 'zoom_token')) {
                $table->string('zoom_token')->nullable();
            }
            if (!Schema::hasColumn('employees', 'google_token')) {
                $table->string('google_token')->nullable();
            }
            if (!Schema::hasColumn('employees', 'ms_token')) {
                $table->string('ms_token')->nullable();
            }
            if (!Schema::hasColumn('employees', 'calendar_id')) {
                $table->string('calendar_id')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['zoom_token', 'google_token', 'ms_token', 'calendar_id']);
        });
    }
};
