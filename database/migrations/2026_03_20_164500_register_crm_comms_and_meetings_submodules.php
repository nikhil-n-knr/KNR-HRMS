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
        $crmModule = DB::table('app_modules')->where('key', 'crm')->first();

        if ($crmModule) {
            $subModules = [
                [
                    'module_id' => $crmModule->id,
                    'name' => 'Communications Hub',
                    'key' => 'communications',
                    'route' => 'crm.hub',
                    'order' => 7,
                    'status' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'module_id' => $crmModule->id,
                    'name' => 'Meetings Hub',
                    'key' => 'meetings',
                    'route' => 'crm.hub',
                    'order' => 8,
                    'status' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ];

            foreach ($subModules as $sub) {
                DB::table('app_sub_modules')->updateOrInsert(
                    ['module_id' => $sub['module_id'], 'key' => $sub['key']],
                    $sub
                );
            }
        }

        // Add support for manual/offline flags in emails
        if (Schema::hasTable('crm_email_messages')) {
            Schema::table('crm_email_messages', function (Blueprint $table) {
                if (!Schema::hasColumn('crm_email_messages', 'is_manual')) {
                    $table->boolean('is_manual')->default(false)->after('status');
                }
                if (!Schema::hasColumn('crm_email_messages', 'source')) {
                    $table->string('source')->nullable()->after('is_manual'); // 'manual', 'sync', 'offline_log'
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Option to remove the columns if needed
    }
};
