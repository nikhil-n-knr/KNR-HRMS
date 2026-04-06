<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CardTemplate;

class ModernCardTemplateSeeder extends Seeder
{
    public function run(): void
    {
        // CLEAR OLD DATA to avoid "Black and Blue" broken states
        CardTemplate::truncate();

        $templates = [
            [
                'name' => 'Corporate Blue',
                'type' => 'Employee',
                'design_data' => [
                    'front' => [
                        'version' => '5.3.0',
                        'objects' => [
                            // Background
                            ['type' => 'rect', 'version' => '5.3.0', 'left' => 0, 'top' => 0, 'width' => 1011, 'height' => 638, 'fill' => '#FFFFFF', 'id' => 'bg'],
                            // Top Header Gradient-like accent
                            ['type' => 'rect', 'version' => '5.3.0', 'left' => 0, 'top' => 0, 'width' => 1011, 'height' => 120, 'fill' => '#1E40AF', 'id' => 'header'],
                            // Side stripe
                            ['type' => 'rect', 'version' => '5.3.0', 'left' => 0, 'top' => 120, 'width' => 30, 'height' => 518, 'fill' => '#3B82F6', 'id' => 'accent_stripe'],
                            // Company Logo Placeholder (Circle)
                            ['type' => 'circle', 'version' => '5.3.0', 'left' => 50, 'top' => 30, 'radius' => 30, 'fill' => '#FFFFFF', 'id' => 'logo_bg'],
                            ['type' => 'i-text', 'version' => '5.3.0', 'left' => 120, 'top' => 45, 'text' => 'CORPORATE ID', 'fontSize' => 30, 'fontFamily' => 'Inter', 'fill' => '#FFFFFF', 'fontWeight' => '900', 'id' => 'header_title'],
                            
                            // Photo Frame
                            ['type' => 'rect', 'version' => '5.3.0', 'left' => 80, 'top' => 200, 'width' => 240, 'height' => 300, 'fill' => '#F3F4F6', 'stroke' => '#D1D5DB', 'strokeWidth' => 2, 'rx' => 10, 'ry' => 10, 'data_binding' => 'photo_placeholder', 'id' => 'photo_frame'],
                            
                            // Employee info
                            ['type' => 'i-text', 'version' => '5.3.0', 'left' => 380, 'top' => 200, 'text' => '{{ Name }}', 'fontSize' => 60, 'fontFamily' => 'Inter', 'fill' => '#111827', 'fontWeight' => 'bold', 'id' => 'name_field'],
                            ['type' => 'i-text', 'version' => '5.3.0', 'left' => 380, 'top' => 280, 'text' => '{{ Designation }}', 'fontSize' => 32, 'fontFamily' => 'Inter', 'fill' => '#6B7280', 'id' => 'role_field'],
                            
                            // ID & Details
                            ['type' => 'i-text', 'version' => '5.3.0', 'left' => 380, 'top' => 400, 'text' => 'EMPLOYEE ID', 'fontSize' => 16, 'fontFamily' => 'Inter', 'fill' => '#9CA3AF', 'fontWeight' => 'bold', 'id' => 'id_label'],
                            ['type' => 'i-text', 'version' => '5.3.0', 'left' => 380, 'top' => 425, 'text' => '{{ ID Number }}', 'fontSize' => 24, 'fontFamily' => 'Inter', 'fill' => '#1E40AF', 'fontWeight' => '900', 'id' => 'id_field'],
                            
                            // Blood Group
                            ['type' => 'i-text', 'version' => '5.3.0', 'left' => 650, 'top' => 400, 'text' => 'BLOOD GROUP', 'fontSize' => 16, 'fontFamily' => 'Inter', 'fill' => '#9CA3AF', 'fontWeight' => 'bold', 'id' => 'bg_label'],
                            ['type' => 'i-text', 'version' => '5.3.0', 'left' => 650, 'top' => 425, 'text' => '{{ Blood Group }}', 'fontSize' => 24, 'fontFamily' => 'Inter', 'fill' => '#DC2626', 'fontWeight' => '900', 'id' => 'bg_field'],
                            
                            // Footer text
                            ['type' => 'i-text', 'version' => '5.3.0', 'left' => 80, 'top' => 560, 'text' => 'This card is electronic property of HRMS Tech Corp.', 'fontSize' => 18, 'fontFamily' => 'Inter', 'fill' => '#9CA3AF', 'id' => 'footer_text'],
                        ]
                    ],
                    'back' => [
                        'version' => '5.3.0',
                        'objects' => [
                            ['type' => 'rect', 'version' => '5.3.0', 'left' => 0, 'top' => 0, 'width' => 1011, 'height' => 638, 'fill' => '#111827', 'id' => 'back_bg'],
                            ['type' => 'rect', 'version' => '5.3.0', 'left' => 0, 'top' => 0, 'width' => 1011, 'height' => 10, 'fill' => '#3B82F6', 'id' => 'top_line'],
                            ['type' => 'i-text', 'version' => '5.3.0', 'left' => 505, 'top' => 319, 'text' => 'IN CASE OF EMERGENCY', 'fontSize' => 30, 'fill' => '#FFFFFF', 'originX' => 'center', 'fontWeight' => '900', 'id' => 'ice_title'],
                            ['type' => 'i-text', 'version' => '5.3.0', 'left' => 505, 'top' => 370, 'text' => 'Please contact HR at +1 (555) 000-1234', 'fontSize' => 20, 'fill' => '#9CA3AF', 'originX' => 'center', 'id' => 'ice_sub'],
                            ['type' => 'rect', 'version' => '5.3.0', 'left' => 400, 'top' => 500, 'width' => 211, 'height' => 80, 'fill' => '#FFFFFF', 'rx' => 5, 'data_binding' => 'qr_code', 'id' => 'qr_ph'],
                        ]
                    ]
                ]
            ],
            [
                'name' => 'Elite Crimson',
                'type' => 'Employee',
                'design_data' => [
                    'front' => [
                        'version' => '5.3.0',
                        'objects' => [
                            ['type' => 'rect', 'version' => '5.3.0', 'left' => 0, 'top' => 0, 'width' => 1011, 'height' => 638, 'fill' => '#1F1B1B', 'id' => 'bg'],
                            ['type' => 'rect', 'version' => '5.3.0', 'left' => 0, 'top' => 0, 'width' => 1011, 'height' => 30, 'fill' => '#8B0000', 'id' => 'top_accent'],
                            ['type' => 'i-text', 'version' => '5.3.0', 'left' => 505, 'top' => 80, 'text' => 'ELITE MEMBER', 'fontSize' => 24, 'fill' => '#FFD700', 'originX' => 'center', 'fontWeight' => 'bold', 'charSpacing' => 200, 'id' => 'title'],
                            
                            // Photo Frame Center
                            ['type' => 'circle', 'version' => '5.3.0', 'left' => 505, 'top' => 150, 'radius' => 120, 'fill' => '#000000', 'stroke' => '#8B0000', 'strokeWidth' => 10, 'originX' => 'center', 'data_binding' => 'photo_placeholder', 'id' => 'photo_frame'],
                            
                            ['type' => 'i-text', 'version' => '5.3.0', 'left' => 505, 'top' => 410, 'text' => '{{ Name }}', 'fontSize' => 50, 'fill' => '#FFFFFF', 'originX' => 'center', 'fontWeight' => '900', 'id' => 'name'],
                            ['type' => 'i-text', 'version' => '5.3.0', 'left' => 505, 'top' => 480, 'text' => '{{ Designation }}', 'fontSize' => 20, 'fill' => '#8B0000', 'originX' => 'center', 'id' => 'role'],
                            
                            // Decorative lines
                            ['type' => 'path', 'version' => '5.3.0', 'left' => 300, 'top' => 550, 'path' => 'M 0 0 L 411 0', 'stroke' => '#FFD700', 'strokeWidth' => 2, 'id' => 'line'],
                        ]
                    ],
                    'back' => null
                ]
            ],
            [
                'name' => 'Visitor Standard',
                'type' => 'Visitor',
                'design_data' => [
                    'front' => [
                        'version' => '5.3.0',
                        'objects' => [
                            ['type' => 'rect', 'version' => '5.3.0', 'left' => 0, 'top' => 0, 'width' => 1011, 'height' => 638, 'fill' => '#FFFFFF', 'id' => 'bg'],
                            ['type' => 'rect', 'version' => '5.3.0', 'left' => 0, 'top' => 0, 'width' => 1011, 'height' => 150, 'fill' => '#F59E0B', 'id' => 'header'],
                            ['type' => 'i-text', 'version' => '5.3.0', 'left' => 505, 'top' => 50, 'text' => 'VISITOR', 'fontSize' => 60, 'fill' => '#FFFFFF', 'originX' => 'center', 'fontWeight' => '900', 'id' => 'title'],
                            
                            ['type' => 'i-text', 'version' => '5.3.0', 'left' => 505, 'top' => 300, 'text' => '{{ Name }}', 'fontSize' => 80, 'fill' => '#1F2937', 'originX' => 'center', 'fontWeight' => '900', 'id' => 'name'],
                            ['type' => 'i-text', 'version' => '5.3.0', 'left' => 505, 'top' => 400, 'text' => 'DATE OF VISIT: {{ Joining Date }}', 'fontSize' => 24, 'fill' => '#6B7280', 'originX' => 'center', 'id' => 'date'],
                            
                            ['type' => 'rect', 'version' => '5.3.0', 'left' => 0, 'top' => 580, 'width' => 1011, 'height' => 58, 'fill' => '#1F2937', 'id' => 'footer'],
                            ['type' => 'i-text', 'version' => '5.3.0', 'left' => 505, 'top' => 595, 'text' => 'Please return this pass at the reception desk.', 'fontSize' => 16, 'fill' => '#FFFFFF', 'originX' => 'center', 'id' => 'f_text'],
                        ]
                    ],
                    'back' => null
                ]
            ]
        ];

        foreach ($templates as $t) {
            CardTemplate::updateOrCreate(
                ['name' => $t['name']],
                [
                    'type' => $t['type'],
                    'dimensions' => ['width' => 1011, 'height' => 638],
                    'design_data' => $t['design_data'],
                    'is_active' => true,
                    'orientation' => 'Landscape'
                ]
            );
        }
    }
}
