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
        Schema::create('cms_segments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->unsignedBigInteger('site_id')->nullable()->index();
            $table->string('name');
            $table->string('description')->nullable();
            $table->enum('logic', ['ALL', 'ANY'])->default('ALL');
            $table->json('rules')->nullable();
            $table->string('color')->nullable();
            $table->string('icon')->nullable();
            $table->integer('member_count')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_segments');
    }
};
