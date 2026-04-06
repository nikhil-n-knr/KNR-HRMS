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
        Schema::table('bug_activities', function (Blueprint $table) {
            $table->dropForeign(['user_id']); // Drops the foreign key constraint
            $table->renameColumn('user_id', 'actor_id');
        });
        
        // Let's do after actor_id in a separate closure to ensure column exists
        Schema::table('bug_activities', function (Blueprint $table) {
            $table->string('actor_type')->nullable()->after('actor_id');
            $table->index(['actor_id', 'actor_type']);
        });
    }

    public function down(): void
    {
        Schema::table('bug_activities', function (Blueprint $table) {
            $table->dropIndex(['actor_id', 'actor_type']);
            $table->dropColumn('actor_type');
            $table->renameColumn('actor_id', 'user_id');
        });

        Schema::table('bug_activities', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });
    }
};
