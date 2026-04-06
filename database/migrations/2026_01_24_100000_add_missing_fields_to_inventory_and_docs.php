<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('inventory_items', function (Blueprint $table) {
            $table->string('category')->nullable()->after('name');
        });

        Schema::table('physical_document_locations', function (Blueprint $table) {
            $table->unsignedBigInteger('parent_id')->nullable()->after('id');
            $table->foreign('parent_id')->references('id')->on('physical_document_locations')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('inventory_items', function (Blueprint $table) {
            $table->dropColumn('category');
        });

        Schema::table('physical_document_locations', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn('parent_id');
        });
    }
};
