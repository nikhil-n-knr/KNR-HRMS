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
        Schema::table('projects', function (Blueprint $table) {
            $table->string('repository_url')->nullable()->after('status');
            $table->string('webhook_secret')->nullable()->after('repository_url');
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->decimal('internal_cost_rate', 10, 2)->nullable()->after('designation');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['repository_url', 'webhook_secret']);
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn('internal_cost_rate');
        });
    }
};
