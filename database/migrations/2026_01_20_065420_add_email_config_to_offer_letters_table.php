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
        Schema::table('offer_letters', function (Blueprint $table) {
            $table->json('email_config')->nullable()->after('token');
        });
    }

    public function down()
    {
        Schema::table('offer_letters', function (Blueprint $table) {
            $table->dropColumn('email_config');
        });
    }
};
