<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SeedIdCardModule extends Seeder
{
    public function run(): void
    {
        // 1. Ensure Module Exists
        $moduleId = DB::table('app_modules')->insertGetId([
            'name' => 'ID Cards',
            'key' => 'id_card_module',
            'icon' => 'identification', 
            'route' => '/identity-cards', // or just a group container
            'status' => true,
            'sidebar_group' => 'Identity & Access',
            'order' => 61,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 2. Sub-Modules
        $subs = [
            [
                'name' => 'All ID Cards',
                'key' => 'id_cards_list',
                'route' => 'identity.index',
                'order' => 1
            ],
            [
                'name' => 'Design Studio',
                'key' => 'card_studio',
                'route' => 'id-card.studio',
                'order' => 2
            ],
            [
                'name' => 'Print Queue',
                'key' => 'print_queue',
                'route' => 'identity.batch', // Assuming batch print is queue
                'order' => 3
            ]
        ];

        foreach ($subs as $sub) {
            DB::table('app_sub_modules')->insert([
                'module_id' => $moduleId,
                'name' => $sub['name'],
                'key' => $sub['key'],
                'route' => $sub['route'],
                'status' => true,
                'order' => $sub['order'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
