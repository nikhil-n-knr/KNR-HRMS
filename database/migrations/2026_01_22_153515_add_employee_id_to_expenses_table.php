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
        Schema::table('expenses', function (Blueprint $table) {
            $table->foreignId('employee_id')->nullable()->constrained()->onDelete('cascade')->after('id');
            $table->string('receipt_path')->nullable()->after('attachment_path'); // Using instead of generic attachment
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropForeign(['employee_id']);
            $table->dropColumn(['employee_id', 'receipt_path']);
        });
    }
};
