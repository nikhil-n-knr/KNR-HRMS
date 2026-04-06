<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('identity_cards', function (Blueprint $table) {
            // Update status default and potentially add metadata for assets
            $table->string('status')->default('Draft')->change(); // Draft, Pending Photo, In Production, Active, Expired/Revoked
            $table->string('asset_version')->nullable()->after('status'); // For Global Branding Sync / Versioning
        });
    }

    public function down()
    {
        Schema::table('identity_cards', function (Blueprint $table) {
            $table->string('status')->default('Active')->change();
            $table->dropColumn('asset_version');
        });
    }
};
