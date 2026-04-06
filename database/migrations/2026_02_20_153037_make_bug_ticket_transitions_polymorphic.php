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
        Schema::table('bug_ticket_transitions', function (Blueprint $table) {
            $table->renameColumn('user_id', 'actor_id');
        });
        Schema::table('bug_ticket_transitions', function (Blueprint $table) {
            $table->string('actor_type')->nullable()->after('actor_id');
        });
    }

    public function down(): void
    {
        Schema::table('bug_ticket_transitions', function (Blueprint $table) {
            $table->dropColumn('actor_type');
            $table->renameColumn('actor_id', 'user_id');
        });
    }
};
