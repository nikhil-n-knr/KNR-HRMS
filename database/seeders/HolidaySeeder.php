<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Holiday;
use Illuminate\Support\Carbon;

class HolidaySeeder extends Seeder
{
    public function run()
    {
        // Clear existing
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Holiday::truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $currentYear = now()->year;

        $holidays = [
            [
                'name' => 'Christmas',
                'date' => Carbon::create($currentYear, 12, 25),
                'type' => 'fixed',
                'is_recurring' => true
            ],
            [
                'name' => 'New Year',
                'date' => Carbon::create($currentYear + 1, 1, 1),
                'type' => 'fixed',
                'is_recurring' => true
            ],
            [
                'name' => 'Republic Day',
                'date' => Carbon::create($currentYear + 1, 1, 26),
                'type' => 'fixed',
                'is_recurring' => true
            ],
            [
                'name' => 'Good Friday',
                'date' => Carbon::create($currentYear + 1, 3, 29), // Approx
                'type' => 'fixed',
                'is_recurring' => false
            ],
            // Floating
            [
                'name' => 'Optional Holiday 1',
                'date' => Carbon::create($currentYear + 1, 2, 14),
                'type' => 'floating',
                'is_recurring' => false
            ]
        ];

        foreach ($holidays as $h) {
            $h['tenant_id'] = 1; // Default Tenant
            Holiday::create($h);
        }
    }
}
