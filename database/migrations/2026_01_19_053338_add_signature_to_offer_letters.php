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
            $table->timestamp('accepted_at')->nullable()->after('status');
            $table->string('accepted_ip')->nullable()->after('accepted_at');
            $table->longText('signature_image')->nullable()->after('accepted_ip'); // Base64
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('offer_letters', function (Blueprint $table) {
            $table->dropColumn(['accepted_at', 'accepted_ip', 'signature_image']);
        });
    }
};
