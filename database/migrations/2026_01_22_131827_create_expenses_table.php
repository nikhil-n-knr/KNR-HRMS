<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->decimal('amount', 12, 2);
            $table->string('currency', 3)->default('INR'); 
            $table->date('incurred_date');
            $table->string('category'); // e.g., 'Payroll', 'Office', 'Travel', 'Software'
            
            $table->string('status')->default('Pending'); // Pending, Approved, Rejected
            $table->foreignId('approved_by')->nullable()->constrained('users');
            
            // Link to Payroll (optional)
            // If this expense is auto-generated from a payroll run
            $table->unsignedBigInteger('payroll_id')->nullable();
            
            $table->text('description')->nullable();
            $table->string('attachment_path')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('expenses');
    }
};
