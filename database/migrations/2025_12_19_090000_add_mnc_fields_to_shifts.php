<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Update Shifts
        Schema::table('shifts', function (Blueprint $table) {
            if (!Schema::hasColumn('shifts', 'color')) {
                $table->string('color', 7)->default('#3B82F6'); // Hex color
            }
            if (!Schema::hasColumn('shifts', 'code')) {
                $table->string('code', 10)->nullable(); // e.g. "GS", "MS"
            }
        });

        // Update Shift Rotations (Aligning Old vs New schema)
        Schema::table('shift_rotations', function (Blueprint $table) {
            if (!Schema::hasColumn('shift_rotations', 'cycle_days')) {
                $table->integer('cycle_days')->default(7);
            }
            // Remove 'frequency' if we are moving to 'cycle_days' generic logic
            // keeping it usually safe, but let's make it nullable if strictly replacing
            if (Schema::hasColumn('shift_rotations', 'frequency')) {
                $table->string('frequency')->nullable()->change();
            }
        });
    }

    public function down(): void
    {
        Schema::table('shifts', function (Blueprint $table) {
            $table->dropColumn(['color', 'code']);
        });
        
        Schema::table('shift_rotations', function (Blueprint $table) {
             if (Schema::hasColumn('shift_rotations', 'cycle_days')) {
                $table->dropColumn('cycle_days');
            }
        });
    }
};
