<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VisitorModuleSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('app_modules')->updateOrInsert(
            ['key' => 'visitor_module'],
            [
                'name' => 'Visitors',
                'icon' => 'user-group', 
                'route' => '/visitors',
                'status' => true,
                'sidebar_group' => 'Identity & Access',
                'order' => 60,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
}
