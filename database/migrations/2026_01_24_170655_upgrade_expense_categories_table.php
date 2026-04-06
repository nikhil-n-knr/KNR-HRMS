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
        Schema::table('expense_categories', function (Blueprint $table) {
            $table->string('code')->nullable()->after('name');
            $table->string('icon')->nullable()->after('code');
            $table->string('unit_type')->default('FIXED')->after('description'); // FIXED, MILEAGE, PER_DIEM
            $table->decimal('unit_rate', 10, 2)->nullable()->after('unit_type');
            
            // JSON Configuration Columns for flexibility
            $table->json('limits')->nullable(); // max_amount, monthly_cap, limit_action
            $table->json('rules')->nullable(); // receipt_threshold, gst_eligible, project_linking
            $table->json('visibility')->nullable(); // departments, grades, dates
            
            $table->integer('sort_order')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expense_categories', function (Blueprint $table) {
            $table->dropColumn(['code', 'icon', 'unit_type', 'unit_rate', 'limits', 'rules', 'visibility', 'sort_order', 'is_active']);
        });
    }
};
