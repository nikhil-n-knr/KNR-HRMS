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
        Schema::table('offer_letters', function (Blueprint $table) {
            $table->json('candidate_preferences')->nullable()->after('signature_image');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('offer_letters', function (Blueprint $table) {
            $table->dropColumn('candidate_preferences');
        });
    }
};
