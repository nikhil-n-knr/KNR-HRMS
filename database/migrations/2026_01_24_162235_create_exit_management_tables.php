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
        // 1. Employee Exits (The Main Record)
        Schema::create('employee_exits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->date('resignation_date');
            $table->date('last_working_day_proposed')->nullable();
            $table->date('last_working_day_approved')->nullable();
            
            $table->string('reason_type'); // Better Opportunity, Personal, etc.
            $table->text('reason_details')->nullable();
            
            $table->string('status')->default('pending'); // pending, approved, rejected, withdrawn, completed
            
            // F&F Meta
            $table->integer('notice_period_days')->default(0);
            $table->integer('shortfall_days')->default(0);
            $table->boolean('notice_waiver')->default(false);
            $table->date('settlement_date')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
        });

        // 2. Department No Dues
        Schema::create('no_dues_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exit_id')->constrained('employee_exits')->cascadeOnDelete();
            $table->string('department'); // IT, Admin, Finance, Manager
            $table->foreignId('approver_id')->nullable()->constrained('users');
            $table->string('status')->default('pending'); // pending, cleared, rejected
            $table->decimal('recovery_amount', 10, 2)->default(0);
            $table->text('remarks')->nullable();
            $table->timestamps();
        });

        // 3. F&F Settlement Line Items
        Schema::create('fnf_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exit_id')->constrained('employee_exits')->cascadeOnDelete();
            $table->string('component_name'); // Notice Pay, Leave Encashment, Gratuity, Asset Recovery
            $table->string('type'); // earning, deduction
            $table->decimal('amount', 10, 2);
            $table->boolean('is_taxable')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fnf_items');
        Schema::dropIfExists('no_dues_approvals');
        Schema::dropIfExists('employee_exits');
    }
};
