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
        Schema::table('git_repositories', function (Blueprint $table) {
            $table->string('webhook_secret')->nullable()->after('clone_url');
            $table->boolean('is_active')->default(true)->after('last_synced_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('git_repositories', function (Blueprint $table) {
            $table->dropColumn(['webhook_secret', 'is_active']);
        });
    }
};
