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
        DB::table('app_sub_modules')
            ->whereIn('key', ['communications', 'meetings'])
            ->update(['route' => 'comms.hub']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('app_sub_modules')
            ->whereIn('key', ['communications', 'meetings'])
            ->update(['route' => 'crm.hub']);
    }
};
