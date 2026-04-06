<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('compliance_rules')) {
            Schema::create('compliance_rules', function (Blueprint $table) {
                $table->id();
                $table->string('component', 20); // 'PF', 'ESI', 'PT'
                $table->date('effective_from');
                
                // Rules stored as JSON for flexibility (wage ceilings, percentages, etc.)
                $table->json('rules_json'); 
                
                $table->boolean('is_active')->default(true);
                $table->text('description')->nullable();
                
                $table->timestamps();
                
                // Indexes
                $table->index(['component', 'is_active']);
                $table->index('effective_from');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('compliance_rules');
    }
};
