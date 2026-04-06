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
        $pmModule = \Illuminate\Support\Facades\DB::table('app_modules')->where('key', 'project_management')->first();

        if ($pmModule) {
            \Illuminate\Support\Facades\DB::table('app_sub_modules')->insertOrIgnore([
                [
                    'module_id' => $pmModule->id,
                    'key' => 'client_hub',
                    'name' => 'Client Hub',
                    'route' => 'clients.index',
                    'status' => true,
                    'order' => 100,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'module_id' => $pmModule->id,
                    'key' => 'workflow_architect',
                    'name' => 'Workflow Architect',
                    'route' => 'workflow-architect.index',
                    'status' => true,
                    'order' => 101,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'module_id' => $pmModule->id,
                    'key' => 'bug_intelligence',
                    'name' => 'Intelligence',
                    'route' => 'bugs.analytics', // Will map to Analytics, which user can switch to ReportBuilder via tab
                    'status' => true,
                    'order' => 102,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        }
    }

    public function down(): void
    {
        \Illuminate\Support\Facades\DB::table('app_sub_modules')->whereIn('key', ['client_hub', 'workflow_architect', 'bug_intelligence'])->delete();
    }
};
