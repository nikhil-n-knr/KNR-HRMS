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
        Schema::table('project_documents', function (Blueprint $table) {
            $table->json('shared_with_teams')->nullable()->after('shared_with');
            // Ensure shared_with is treated as users array. 
            // We might rename shared_with to shared_with_users in future but for now keep as is.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_documents', function (Blueprint $table) {
            $table->dropColumn('shared_with_teams');
        });
    }
};
