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
        Schema::table('crm_marketing_campaigns', function (Blueprint $table) {
            $table->integer('predicted_success_score')->nullable()->after('stats');
            $table->text('ai_optimization_tips')->nullable()->after('predicted_success_score');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('crm_marketing_campaigns', function (Blueprint $table) {
            $table->dropColumn(['predicted_success_score', 'ai_optimization_tips']);
        });
    }
};
