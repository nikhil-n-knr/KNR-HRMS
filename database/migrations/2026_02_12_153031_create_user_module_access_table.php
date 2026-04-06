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
        Schema::create('user_module_access', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('module_id')->constrained('modules')->onDelete('cascade');
            
            // Access control
            $table->boolean('is_enabled')->default(true);
            $table->foreignId('granted_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('granted_at')->nullable();
            
            // Module-specific role (e.g., crm_admin, crm_sales_rep, crm_support)
            $table->string('module_role', 100)->nullable();
            
            $table->timestamps();
            
            $table->unique(['user_id', 'module_id']);
            $table->index(['tenant_id', 'module_id']);
            $table->index('is_enabled');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_module_access');
    }
};
