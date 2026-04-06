<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UpdateLMSRoutesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Update LMS Analytics route to match web.php
        DB::table('app_sub_modules')
            ->where('key', 'analytics')
            ->where('route', 'hr.lms.analytics')
            ->update(['route' => 'hr.lms.analytics.index']);

        $this->command->info('✅ LMS Analytics route updated to hr.lms.analytics.index');
    }
}
