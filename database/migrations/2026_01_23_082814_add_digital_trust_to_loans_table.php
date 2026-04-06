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
        Schema::table('loans', function (Blueprint $table) {
            $table->foreignId('loan_product_id')->nullable()->after('loan_type')->constrained(); // Link to Product
            
            // Digital Trust
            $table->timestamp('signed_at')->nullable();
            $table->string('signer_ip')->nullable();
            $table->string('signature_hash')->nullable(); // e.g. from E-Sign provider
            
            // Rule Snapshot (to lock in rates even if configs change later)
            $table->decimal('interest_rate_applied', 5, 2)->default(0);
            $table->string('interest_type_applied')->nullable(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            //
        });
    }
};
