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
        Schema::table('tax_declarations', function (Blueprint $table) {
            $table->boolean('is_disputed')->default(false);
            $table->text('dispute_reason')->nullable();
        });

        Schema::table('employee_hra_declarations', function (Blueprint $table) {
            $table->boolean('is_disputed')->default(false);
            $table->text('dispute_reason')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tax_declarations', function (Blueprint $table) {
            $table->dropColumn(['is_disputed', 'dispute_reason']);
        });

        Schema::table('employee_hra_declarations', function (Blueprint $table) {
            $table->dropColumn(['is_disputed', 'dispute_reason']);
        });
    }
};
