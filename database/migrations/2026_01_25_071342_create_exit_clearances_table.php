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
        Schema::create('exit_clearances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            
            // The department responsible for this clearance (e.g., IT, Finance, Admin)
            // Can be null if it's a general clearance item, but usually linked to a Dept.
            $table->foreignId('department_id')->nullable()->constrained();
            
            // Or use a Type string for flexibility (e.g. 'IT Assets', 'Library', 'Canteen')
            $table->string('type')->default('Department'); // 'Department', 'Asset', 'Loan'
            
            $table->string('module')->nullable(); // 'it', 'finance', 'admin', 'hr' (System Key)
            
            $table->enum('status', ['Pending', 'Cleared', 'Rejected', 'Hold'])->default('Pending');
            
            $table->decimal('due_amount', 12, 2)->default(0);
            $table->text('remarks')->nullable();
            
            $table->foreignId('cleared_by')->nullable()->constrained('users');
            $table->timestamp('cleared_at')->nullable();
            
            $table->timestamps();
            
            // Index for faster lookup
            $table->index(['employee_id', 'module']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exit_clearances');
    }
};
