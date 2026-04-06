<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('crm_meetings', function (Blueprint $table) {
            if (!Schema::hasColumn('crm_meetings', 'uuid')) {
                $table->uuid('uuid')->nullable()->after('id')->unique();
            }
        });

        // Fill existing meetings with UUIDs
        DB::table('crm_meetings')->whereNull('uuid')->get()->each(function ($meeting) {
            DB::table('crm_meetings')->where('id', $meeting->id)->update(['uuid' => Str::uuid()]);
        });

        Schema::table('crm_meetings', function (Blueprint $table) {
            $table->uuid('uuid')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('crm_meetings', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });
    }
};
