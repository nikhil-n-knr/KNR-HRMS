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
            // Columns for Finance Settlement
            if (!Schema::hasColumn('expenses', 'payout_method')) {
                $table->string('payout_method')->nullable()->default('payroll')->after('currency'); // payroll, direct
            }
            if (!Schema::hasColumn('expenses', 'transaction_reference')) {
                $table->string('transaction_reference')->nullable()->after('payout_method');
            }
            if (!Schema::hasColumn('expenses', 'settlement_date')) {
                $table->date('settlement_date')->nullable()->after('transaction_reference');
            }
            if (!Schema::hasColumn('expenses', 'reimbursed_on')) {
                $table->date('reimbursed_on')->nullable()->after('settlement_date');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropColumn(['payout_method', 'transaction_reference', 'settlement_date', 'reimbursed_on']);
        });
    }
};
