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
        Schema::table('expenses', function (Blueprint $table) {
            if (!Schema::hasColumn('expenses', 'currency')) {
                 $table->string('currency', 3)->default('INR')->after('amount');
            }
            if (!Schema::hasColumn('expenses', 'exchange_rate')) {
                $table->decimal('exchange_rate', 10, 6)->default(1.0)->after('currency');
            }
            if (!Schema::hasColumn('expenses', 'gst_number')) {
                $table->string('gst_number')->nullable()->after('exchange_rate');
            }
            if (!Schema::hasColumn('expenses', 'approved_amount')) {
                $table->decimal('approved_amount', 12, 2)->nullable()->after('amount');
            }
            if (!Schema::hasColumn('expenses', 'is_duplicate_flag')) {
                $table->boolean('is_duplicate_flag')->default(false)->after('status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropColumn(['exchange_rate', 'gst_number', 'approved_amount', 'is_duplicate_flag']);
        });
    }
};
