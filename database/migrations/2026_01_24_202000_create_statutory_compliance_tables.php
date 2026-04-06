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
        // 1. Compliance Configuration
        if (!Schema::hasTable('compliance_configs')) {
            Schema::create('compliance_configs', function (Blueprint $table) {
                $table->id();
                $table->string('type'); // 'pf', 'esi', 'pt', 'tds'
                $table->string('key'); // 'employer_code', 'wage_ceiling'
                $table->text('value')->nullable(); // Can be simple value or JSON
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // 2. Compliance Challans (Payment Tracking)
        if (!Schema::hasTable('compliance_challans')) {
            Schema::create('compliance_challans', function (Blueprint $table) {
                $table->id();
                $table->string('type'); // 'pf', 'esi', 'pt', 'tds'
                $table->integer('month');
                $table->integer('year');
                $table->decimal('amount_paid', 15, 2)->default(0);
                $table->string('transaction_ref')->nullable(); // TRRN
                $table->date('payment_date')->nullable();
                $table->string('document_path')->nullable(); // Receipt
                $table->string('status')->default('pending'); // pending, paid
                $table->timestamps();
            });
        }

        // 3. Add Compliance Fields to Employees
        Schema::table('employees', function (Blueprint $table) {
            if (!Schema::hasColumn('employees', 'uan_number')) {
                $table->string('uan_number')->nullable()->after('status');
            }
            if (!Schema::hasColumn('employees', 'esi_number')) {
                $table->string('esi_number')->nullable()->after('uan_number');
            }
            if (!Schema::hasColumn('employees', 'pan_number')) {
                $table->string('pan_number')->nullable()->after('esi_number');
            }
            if (!Schema::hasColumn('employees', 'aadhaar_number')) {
                $table->string('aadhaar_number')->nullable()->after('pan_number');
            }
            if (!Schema::hasColumn('employees', 'pf_ceiling_applied')) {
                $table->boolean('pf_ceiling_applied')->default(true)->after('aadhaar_number');
            }
            if (!Schema::hasColumn('employees', 'is_international_worker')) {
                $table->boolean('is_international_worker')->default(false)->after('pf_ceiling_applied');
            }
            if (!Schema::hasColumn('employees', 'is_director')) {
                $table->boolean('is_director')->default(false)->after('is_international_worker');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn([
                'uan_number', 'esi_number', 'pan_number', 'aadhaar_number',
                'pf_ceiling_applied', 'is_international_worker', 'is_director'
            ]);
        });
        Schema::dropIfExists('compliance_challans');
        Schema::dropIfExists('compliance_configs');
    }
};
