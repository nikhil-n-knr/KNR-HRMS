<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('comments', function (Blueprint $table) {
            // Add user_type column for polymorphic author
            $table->string('user_type')->after('user_id')->nullable();
        });

        // Seed existing comments with the default App\Models\User type
        DB::table('comments')->update(['user_type' => 'App\Models\User']);
        
        Schema::table('comments', function (Blueprint $table) {
             // Now make it NOT nullable if we want to enforce it, 
             // but keep nullable for safety during transition
             // In Laravel, we can't easily drop foreign keys in all DB drivers (like SQLite) without dropping the table.
             // We will leave the user_id foreign key as is for now, but allow it to be polymorphic by using user_type.
             // If a comment comes from a ClientUser, we will store ClientUser ID in user_id and 'App\Models\ClientUser' in user_type.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('comments', function (Blueprint $table) {
            $table->dropColumn('user_type');
        });
    }
};
