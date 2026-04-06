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
        Schema::table('identity_cards', function (Blueprint $table) {
            $table->foreignId('template_id')->nullable()->constrained('card_templates')->onDelete('set null')->after('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('identity_cards', function (Blueprint $table) {
            //
        });
    }
};
