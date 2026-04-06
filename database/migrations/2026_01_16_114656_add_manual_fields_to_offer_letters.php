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
            $table->string('manual_path')->nullable()->after('token');
            $table->json('attachments')->nullable()->after('manual_path'); // For Policy Docs/Welcome Letter
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('offer_letters', function (Blueprint $table) {
            //
        });
    }
};
