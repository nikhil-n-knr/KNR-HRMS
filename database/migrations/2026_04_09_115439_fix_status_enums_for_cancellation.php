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
        // Convert ENUMs to string to allow more statuses (Cancelled, Withdrawal Requested, etc.)
        Schema::table('wfh_requests', function (Blueprint $table) {
            $table->string('status')->default('Pending')->change();
        });

        Schema::table('shift_swaps', function (Blueprint $table) {
            $table->string('status')->default('Pending')->change();
        });

        Schema::table('floating_holiday_requests', function (Blueprint $table) {
            $table->string('status')->default('Requested')->change();
        });

        Schema::table('leave_requests', function (Blueprint $table) {
            $table->string('status')->default('pending')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverting for one (WFH) as example, but usually we just keep it string for flexibility
        Schema::table('wfh_requests', function (Blueprint $table) {
            $table->enum('status', ['Pending', 'Approved', 'Rejected'])->default('Pending')->change();
        });
    }
};
