<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CardTemplateSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Corporate Blue
        $blueJson = file_get_contents(__DIR__ . '/templates/corporate_blue.json');
        $blueData = json_decode($blueJson, true); // Get the inner object if wrapped, or use direct
        // Adjust based on my json file structure above, I wrapped it in a key.
        $blueElements = $blueData['corporate_blue'];

        DB::table('card_templates')->updateOrInsert(
            ['name' => 'Corporate Blue'],
            [
                'type' => 'Standard',
                'dimensions' => json_encode(['width' => 1011, 'height' => 638]),
                'design_data' => json_encode(['front' => $blueElements, 'back' => null]),
                'orientation' => 'Landscape',
                'is_active' => true,
                'tenant_id' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ]
        );

        // 2. Visitor Pass (Simple)
        $visitorJson = [
            'version' => '5.3.0',
            'objects' => [
                 [
                    'type' => 'rect',
                    'left' => 0, 'top' => 0, 'width' => 1011, 'height' => 638, 'fill' => '#fffbeb' // Yellowish
                 ],
                 [
                    'type' => 'rect',
                    'left' => 0, 'top' => 0, 'width' => 1011, 'height' => 150, 'fill' => '#fbbf24'
                 ],
                 [
                    'type' => 'text',
                    'left' => 350, 'top' => 45, 'text' => 'VISITOR PASS', 'fontSize' => 60, 'fontWeight' => 'bold', 'fill' => '#000000'
                 ],
                 [
                    'type' => 'text',
                    'left' => 100, 'top' => 250, 'text' => 'Name: {{ name }}', 'fontSize' => 40
                 ],
                 [
                    'type' => 'text',
                    'left' => 100, 'top' => 320, 'text' => 'Host: {{ host_name }}', 'fontSize' => 30
                 ],
                 [
                    'type' => 'text',
                    'left' => 100, 'top' => 380, 'text' => 'Valid Until: {{ valid_until }}', 'fontSize' => 30, 'fill' => '#ef4444'
                 ]
            ]
        ];

        DB::table('card_templates')->updateOrInsert(
            ['name' => 'Visitor Standard'],
            [
                'type' => 'Visitor',
                'dimensions' => json_encode(['width' => 1011, 'height' => 638]),
                'design_data' => json_encode(['front' => $visitorJson, 'back' => null]),
                'orientation' => 'Landscape',
                'is_active' => true,
                'tenant_id' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ]
        );
        // 3. Vendor / Contractor (Orange)
        $vendorJson = file_get_contents(__DIR__ . '/templates/vendor_orange.json');
        
        DB::table('card_templates')->updateOrInsert(
            ['name' => 'Vendor / Contractor (Orange)'],
            [
                'type' => 'Vendor',
                'dimensions' => json_encode(['width' => 1011, 'height' => 638]),
                'design_data' => json_encode(['front' => json_decode($vendorJson, true), 'back' => null]),
                'orientation' => 'Landscape',
                'is_active' => true,
                'tenant_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
                'preview_image' => null 
            ]
        );
    }
}
