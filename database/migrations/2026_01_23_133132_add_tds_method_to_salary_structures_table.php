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
        Schema::table('salary_structures', function (Blueprint $table) {
            $table->string('tds_method')->default('Auto_New')->after('name'); 
            // Options: Manual, Auto_New, Auto_Old, Employee_Choice
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('salary_structures', function (Blueprint $table) {
            $table->dropColumn('tds_method');
        });
    }
};
