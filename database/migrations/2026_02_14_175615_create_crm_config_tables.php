<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Products & Pricing
        Schema::create('crm_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->string('name');
            $table->string('sku')->nullable()->index();
            $table->text('description')->nullable();
            $table->decimal('price', 15, 2);
            $table->string('currency', 3)->default('USD');
            $table->string('category')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // 2. Custom Fields Definition
        Schema::create('crm_custom_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->string('entity_type'); // 'lead', 'contact', 'account', 'deal'
            $table->string('label');
            $table->string('name'); // internal name
            $table->string('type'); // 'text', 'number', 'select', 'date', 'boolean'
            $table->json('options')->nullable(); // For select type
            $table->boolean('is_required')->default(false);
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        // 3. Add custom_fields JSON column to existing CRM tables
        $tables = ['crm_leads', 'crm_contacts', 'crm_accounts', 'crm_deals'];
        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->json('custom_fields')->nullable()->after('notes');
                });
            }
        }
    }

    public function down(): void
    {
        $tables = ['crm_leads', 'crm_contacts', 'crm_accounts', 'crm_deals'];
        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropColumn('custom_fields');
                });
            }
        }
        Schema::dropIfExists('crm_custom_fields');
        Schema::dropIfExists('crm_products');
    }
};
