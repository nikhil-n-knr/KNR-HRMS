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
        Schema::table('crm_leads', function (Blueprint $table) {
            $table->integer('health_score')->default(100)->after('score');
            $table->decimal('ai_conversion_probability', 5, 2)->nullable()->after('health_score');
            $table->json('ai_insights')->nullable()->after('ai_conversion_probability');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('crm_leads', function (Blueprint $table) {
            $table->dropColumn(['health_score', 'ai_conversion_probability', 'ai_insights']);
        });
    }
};
