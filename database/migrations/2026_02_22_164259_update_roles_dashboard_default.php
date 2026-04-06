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
        Schema::table('roles', function (Blueprint $table) {
            $table->string('dashboard')->nullable()->default(null)->change();
        });

        // Convert existing '/dashboard' to NULL so they fall back to heuristics (Auto-detect)
        \Illuminate\Support\Facades\DB::table('roles')
            ->where('dashboard', '/dashboard')
            ->update(['dashboard' => null]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->string('dashboard')->nullable()->default('/dashboard')->change();
        });
    }
};
