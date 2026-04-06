<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('crm_meetings', function (Blueprint $blueprint) {
            $blueprint->string('venue_phone')->nullable()->after('location');
            $blueprint->string('venue_building')->nullable()->after('venue_phone');
            $blueprint->string('venue_room')->nullable()->after('venue_building');
        });
    }

    public function down(): void
    {
        Schema::table('crm_meetings', function (Blueprint $blueprint) {
            $blueprint->dropColumn(['venue_phone', 'venue_building', 'venue_room']);
        });
    }
};
