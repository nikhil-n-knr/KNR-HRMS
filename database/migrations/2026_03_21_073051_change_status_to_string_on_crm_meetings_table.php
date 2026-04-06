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
        Schema::table('crm_meetings', function (Blueprint $table) {
            $table->string('status', 32)->nullable()->default('pending')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('crm_meetings', function (Blueprint $table) {
            // Cannot reliably revert back to ENUM without potentially losing data if non-enum values were added
            // Better to keep it string for safety, but if we must revert to the original:
            // $table->enum('status', ['scheduled', 'completed', 'cancelled', 'no_show'])->default('scheduled')->change();
        });
    }
};
