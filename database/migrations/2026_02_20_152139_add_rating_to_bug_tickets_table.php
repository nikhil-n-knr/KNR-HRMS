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
        Schema::table('bug_tickets', function (Blueprint $table) {
            $table->tinyInteger('rating')->nullable();
            $table->text('rating_feedback')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bug_tickets', function (Blueprint $table) {
            $table->dropColumn(['rating', 'rating_feedback']);
        });
    }
};
