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
            $table->decimal('hours_spent', 8, 2)->nullable()->after('is_client_visible');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bug_tickets', function (Blueprint $table) {
            $table->dropColumn('hours_spent');
        });
    }
};
