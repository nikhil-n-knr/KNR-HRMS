<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->integer('month'); // 1-12
            $table->integer('year');
            $table->string('batch_name')->nullable(); // "January 2026 Payroll"
            
            $table->date('start_date');
            $table->date('end_date'); // Attendance cycle usually
            
            $table->string('status')->default('Draft'); // Draft, Locked, Paid
            $table->decimal('total_payout', 15, 2)->default(0);
            
            $table->foreignId('processed_by')->constrained('users');
            $table->timestamp('processed_at')->nullable();
            
            // Workflow fields
            $table->foreignId('workflow_id')->nullable()->constrained('workflows')->nullOnDelete();
            $table->foreignId('current_stage_id')->nullable()->constrained('workflow_stages')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('rejection_reason')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('payslips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_id')->constrained()->onDelete('cascade');
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            
            $table->string('payslip_number')->unique()->nullable();
            
            // Totals
            $table->decimal('basic_salary', 12, 2)->default(0);
            $table->decimal('gross_earnings', 12, 2)->default(0);
            $table->decimal('gross_deductions', 12, 2)->default(0);
            $table->decimal('net_pay', 12, 2)->default(0);
            
            // Attendance Stats for this period
            $table->integer('payable_days')->default(30);
            $table->integer('lop_days')->default(0);
            
            // Breakdowns
            $table->json('earnings_breakdown')->nullable(); // Snapshot of earnings
            $table->json('deductions_breakdown')->nullable(); // Snapshot of deductions
            
            $table->string('status')->default('Generated'); // Generated, Emailed
            $table->string('file_path')->nullable(); // PDF Path
            
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('payslips');
        Schema::dropIfExists('payrolls');
    }
};
