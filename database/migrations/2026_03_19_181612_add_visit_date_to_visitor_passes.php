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
            if (!Schema::hasColumn('visitor_passes', 'visit_date')) {
                $table->date('visit_date')->nullable()->after('event_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('visitor_passes', function (Blueprint $table) {
            $table->dropColumn('visit_date');
        });
    }
};
