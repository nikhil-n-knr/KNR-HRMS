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
            $table->enum('payout_method', ['payroll', 'direct'])->default('payroll')->after('status');
            $table->string('transaction_ref')->nullable()->after('payout_method');
            $table->timestamp('paid_at')->nullable()->after('transaction_ref');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropColumn(['payout_method', 'transaction_ref', 'paid_at']);
        });
    }
};
