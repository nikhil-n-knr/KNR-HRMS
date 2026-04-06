<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add 'Converted' and 'Cancelled' to purchase_requests.status enum
        DB::statement("ALTER TABLE purchase_requests MODIFY COLUMN status ENUM('Draft','Approved','Ordered','Received','Converted','Cancelled') DEFAULT 'Draft'");

        // Add currency settings table (if not already created by another migration)
        if (!Schema::hasTable('system_settings')) {
            Schema::create('system_settings', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique();
                $table->text('value')->nullable();
                $table->string('group')->default('general');
                $table->timestamps();
            });
        }

        // Upsert default settings
        $now = now();
        foreach ([
            ['key' => 'currency_code',   'value' => 'INR',          'group' => 'finance'],
            ['key' => 'currency_symbol', 'value' => '₹',            'group' => 'finance'],
            ['key' => 'currency_name',   'value' => 'Indian Rupee', 'group' => 'finance'],
            ['key' => 'date_format',     'value' => 'DD/MM/YYYY',   'group' => 'general'],
            ['key' => 'timezone',        'value' => 'Asia/Kolkata',  'group' => 'general'],
        ] as $row) {
            DB::table('system_settings')->updateOrInsert(
                ['key' => $row['key']],
                array_merge($row, ['updated_at' => $now, 'created_at' => $now])
            );
        }
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE purchase_requests MODIFY COLUMN status ENUM('Draft','Approved','Ordered','Received') DEFAULT 'Draft'");
        Schema::dropIfExists('system_settings');
    }
};
