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
            $table->foreignId('document_template_id')->nullable()->constrained('document_templates')->onDelete('set null');
            $table->foreignId('salary_structure_id')->nullable()->constrained('salary_structures')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('offer_letters', function (Blueprint $table) {
            $table->dropForeign(['document_template_id']);
            $table->dropForeign(['salary_structure_id']);
            $table->dropColumn(['document_template_id', 'salary_structure_id']);
        });
    }
};
