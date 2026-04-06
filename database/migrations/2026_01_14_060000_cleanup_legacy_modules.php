<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Remove legacy modules if they exist to avoid duplication
        // The user wants only ONE "Tax/Recruitment" module. 
        // We will keep 'Talent' (key='talent') as the single source of truth.
        // We delete others: 'recruitment', 'onboarding', 'appointment_letter'
        
        DB::table('app_modules')->whereIn('key', ['recruitment', 'onboarding', 'appointment_letter', 'appointment'])->delete();
        
        // Also ensure sub-modules are cleaned if cascade didn't catch them (safety)
        // Submodules might rely on module_id, so deleting parent likely hid them, but explicit cleanup is good.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No reverse needed for cleanup
    }
};
