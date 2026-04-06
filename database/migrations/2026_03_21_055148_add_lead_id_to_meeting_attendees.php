<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('crm_meeting_attendees', function (Blueprint $blueprint) {
            $blueprint->unsignedBigInteger('lead_id')->nullable()->after('contact_id');
        });
    }

    public function down(): void
    {
        Schema::table('crm_meeting_attendees', function (Blueprint $blueprint) {
            $blueprint->dropColumn('lead_id');
        });
    }
};
