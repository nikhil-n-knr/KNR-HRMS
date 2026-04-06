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
        if (!Schema::hasTable('holidays')) {
            Schema::create('holidays', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->date('date');
                $table->string('type')->default('fixed'); // fixed, floating
                $table->json('applies_to_locations')->nullable();
                $table->boolean('is_recurring')->default(true);
                $table->foreignId('tenant_id')->nullable(); // Determine if tenant specific or global
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('holidays');
    }
};
