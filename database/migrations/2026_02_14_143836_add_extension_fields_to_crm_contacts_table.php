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
        Schema::table('crm_contacts', function (Blueprint $table) {
            $table->string('secondary_email')->nullable()->after('email');
            $table->string('work_phone')->nullable()->after('phone');
            $table->json('social_links')->nullable()->after('notes'); // e.g. {"facebook": "...", "twitter": "..."}
            $table->date('date_of_birth')->nullable()->after('last_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('crm_contacts', function (Blueprint $table) {
            $table->dropColumn(['secondary_email', 'work_phone', 'social_links', 'date_of_birth']);
        });
    }
};
