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
        Schema::table('crm_contacts', function (Blueprint $table) {
            $table->foreignId('owner_id')->nullable()->constrained('users')->after('tenant_id');
        });

        Schema::table('crm_deals', function (Blueprint $table) {
            $table->foreignId('owner_id')->nullable()->constrained('users')->after('tenant_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('crm_contacts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('owner_id');
        });

        Schema::table('crm_deals', function (Blueprint $table) {
            $table->dropConstrainedForeignId('owner_id');
        });
    }
};
