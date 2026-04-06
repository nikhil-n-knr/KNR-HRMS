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
        // 1. WFH Requests: Bypassing IP/Biometric
        Schema::create('wfh_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->text('reason')->nullable();
            $table->enum('status', ['Pending', 'Approved', 'Rejected'])->default('Pending');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });

        // 2. Timesheets: Project-wise tracking
        Schema::create('timesheets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->string('project_name')->nullable();
            $table->text('task_description');
            $table->decimal('hours_spent', 4, 2); // e.g., 4.50 hours
            $table->enum('status', ['Draft', 'Submitted', 'Approved', 'Rejected'])->default('Draft');
            $table->timestamps();
        });

        // 3. Shift Swaps: Peer-to-Peer
        Schema::create('shift_swaps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requester_id')->constrained('employees');
            $table->foreignId('recipient_id')->constrained('employees');
            $table->foreignId('shift_id_from')->constrained('shifts');
            $table->foreignId('shift_id_to')->constrained('shifts');
            $table->date('date');
            $table->enum('status', ['Pending', 'Accepted', 'Approved', 'Rejected'])->default('Pending'); // Accepted by peer, Approved by manager
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamps();
        });

        // 4. Floating Holiday Allocations (Quota)
        Schema::create('floating_holiday_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->year('year');
            $table->integer('total_quota')->default(2);
            $table->integer('used_count')->default(0);
            $table->timestamps();
            $table->unique(['user_id', 'year']);
        });

        // 5. Floating Holiday Requests (The actual booking)
        Schema::create('floating_holiday_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('holiday_id')->constrained()->cascadeOnDelete(); // Must be type="Restricted"
            $table->enum('status', ['Requested', 'Approved', 'Rejected'])->default('Requested');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('floating_holiday_requests');
        Schema::dropIfExists('floating_holiday_allocations');
        Schema::dropIfExists('shift_swaps');
        Schema::dropIfExists('timesheets');
        Schema::dropIfExists('wfh_requests');
    }
};
