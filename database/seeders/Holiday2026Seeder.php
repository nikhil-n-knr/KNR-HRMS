<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Holiday;

class Holiday2026Seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $holidays = [
            ['date' => '2026-01-01', 'name' => 'New Year', 'type' => 'Fixed'],
            ['date' => '2026-01-14', 'name' => 'Makara Sankranti', 'type' => 'Fixed'],
            ['date' => '2026-01-26', 'name' => 'Republic Day', 'type' => 'Fixed'],
            ['date' => '2026-03-20', 'name' => 'Ugadi', 'type' => 'Fixed'],
            ['date' => '2026-05-01', 'name' => 'May Day', 'type' => 'Fixed'],
            ['date' => '2026-09-15', 'name' => 'Ganesh Chaturthi', 'type' => 'Fixed'],
            ['date' => '2026-10-02', 'name' => 'Gandhi Jayanti', 'type' => 'Fixed'],
            ['date' => '2026-10-20', 'name' => 'Maha Navami', 'type' => 'Fixed'],
            ['date' => '2026-10-21', 'name' => 'Vijaya Dashami', 'type' => 'Fixed'],
            ['date' => '2026-11-09', 'name' => 'Deepavali Holiday', 'type' => 'Fixed'],
            ['date' => '2026-12-25', 'name' => 'Christmas Day', 'type' => 'Fixed'],
        ];

        foreach ($holidays as $data) {
            Holiday::updateOrCreate(
                ['date' => $data['date'], 'name' => $data['name']],
                [
                    'type' => $data['type'],
                    'is_recurring' => true,
                    'applies_to_locations' => null,
                    'tenant_id' => 1 // Assuming default tenant
                ]
            );
        }
    }
}
