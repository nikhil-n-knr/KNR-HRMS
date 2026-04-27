<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('asset_categories', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->foreignId('parent_id')->nullable()->after('tenant_id')->constrained('asset_categories')->nullOnDelete();
            $table->string('code')->nullable()->after('name');
            $table->string('lifecycle_type')->nullable()->after('is_electronic');
            $table->unsignedBigInteger('depreciation_rule_id')->nullable()->after('maintenance_interval_days');
            $table->unsignedBigInteger('maintenance_policy_id')->nullable()->after('depreciation_rule_id');

            $table->index(['tenant_id', 'parent_id']);
        });
    }

    public function down(): void
    {
        Schema::table('asset_categories', function (Blueprint $table) {
            $table->dropIndex(['tenant_id', 'parent_id']);
            $table->dropConstrainedForeignId('parent_id');
            $table->dropConstrainedForeignId('tenant_id');
            $table->dropColumn([
                'code',
                'lifecycle_type',
                'depreciation_rule_id',
                'maintenance_policy_id',
            ]);
        });
    }
};
