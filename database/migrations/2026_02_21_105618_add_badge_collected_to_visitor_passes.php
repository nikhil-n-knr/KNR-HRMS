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
        Schema::table('visitor_passes', function (Blueprint $table) {
            $table->timestamp('badge_collected_at')->nullable()->after('badge_printed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('visitor_passes', function (Blueprint $table) {
            $table->dropColumn('badge_collected_at');
        });
    }
};
