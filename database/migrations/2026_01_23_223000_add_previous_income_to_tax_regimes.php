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
        Schema::table('employee_tax_regimes', function (Blueprint $table) {
            $table->decimal('previous_gross_income', 10, 2)->default(0)->after('regime');
            $table->decimal('previous_tds_paid', 10, 2)->default(0)->after('previous_gross_income');
            $table->decimal('previous_pf_deducted', 10, 2)->default(0)->after('previous_tds_paid');
            $table->decimal('previous_pt_paid', 10, 2)->default(0)->after('previous_pf_deducted');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_tax_regimes', function (Blueprint $table) {
            $table->dropColumn([
                'previous_gross_income',
                'previous_tds_paid',
                'previous_pf_deducted',
                'previous_pt_paid'
            ]);
        });
    }
};
