<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('comp_off_credits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('users')->onDelete('cascade');
            $table->date('date_earned'); // The weekend/holiday worked
            $table->integer('minutes_earned')->default(480);
            $table->date('expiry_date');
            $table->enum('status', ['Available', 'Used', 'Expired'])->default('Available');
            $table->foreignId('used_for_leave_id')->nullable(); // Link to leave request if used
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('comp_off_credits');
    }
};
