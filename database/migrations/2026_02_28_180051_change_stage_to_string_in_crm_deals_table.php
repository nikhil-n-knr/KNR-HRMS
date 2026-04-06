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
        Schema::table('crm_deals', function (Blueprint $table) {
            $table->string('stage', 100)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('crm_deals', function (Blueprint $table) {
            $table->enum('stage', [
                'lead', 
                'qualified', 
                'proposal', 
                'negotiation', 
                'closed_won', 
                'closed_lost'
            ])->default('lead')->change();
        });
    }
};
