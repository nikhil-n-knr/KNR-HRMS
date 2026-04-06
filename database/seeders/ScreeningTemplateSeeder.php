<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ScreeningTemplate;
use App\Models\JobPosting;

class ScreeningTemplateSeeder extends Seeder
{
    public function run()
    {
        // 1. Create General Template
        $template = ScreeningTemplate::firstOrCreate(
            ['name' => 'General Screening'],
            [
                'created_by' => 1, 
                'questions' => [
                    [ 'id' => 'q1', 'type' => 'text', 'label' => 'Why do you want to join us?', 'validation' => ['required' => true] ],
                    [ 'id' => 'q2', 'type' => 'number', 'label' => 'Expected CTC (LPA)', 'validation' => ['required' => true] ],
                    [ 'id' => 'q3', 'type' => 'select', 'label' => 'Notice Period', 'options' => ['Immediate', '15 Days', '30 Days', '60 Days', '90 Days'], 'validation' => ['required' => true] ]
                ]
            ]
        );

        // 2. Technical Template (Developer)
        ScreeningTemplate::firstOrCreate(
            ['name' => 'Technical Screening (Senior Dev)'],
            [
                'created_by' => 1,
                'questions' => [
                    [ 'id' => 'tech_1', 'type' => 'text', 'label' => 'Describe a complex system you built.', 'validation' => ['required' => true] ],
                    [ 'id' => 'tech_2', 'type' => 'number', 'label' => 'Years of Laravel Experience', 'validation' => ['required' => true] ],
                    [ 'id' => 'tech_3', 'type' => 'checkbox', 'label' => 'Tech Stack Proficiency', 'options' => ['PHP', 'Vue.js', 'React', 'AWS', 'Docker'], 'validation' => ['required' => false] ],
                    [ 'id' => 'tech_4', 'type' => 'text', 'label' => 'GitHub Profile URL', 'validation' => ['required' => true] ]
                ]
            ]
        );

        // 3. Design Template
        ScreeningTemplate::firstOrCreate(
            ['name' => 'Design Portfolio Review'],
            [
                'created_by' => 1,
                'questions' => [
                    [ 'id' => 'des_1', 'type' => 'text', 'label' => 'Portfolio Link (Behance/Dribbble)', 'validation' => ['required' => true] ],
                    [ 'id' => 'des_2', 'type' => 'select', 'label' => 'Preferred Tool', 'options' => ['Figma', 'Adobe XD', 'Sketch', 'Photoshop'], 'validation' => ['required' => true] ],
                    [ 'id' => 'des_3', 'type' => 'video', 'label' => 'Walk us through your design process (Video)', 'validation' => ['required' => false] ]
                ]
            ]
        );

        // 4. Sales Template
        ScreeningTemplate::firstOrCreate(
            ['name' => 'Sales Executive Assessment'],
            [
                'created_by' => 1,
                'questions' => [
                    [ 'id' => 'sales_1', 'type' => 'number', 'label' => 'Annual Revenue Target Achieved (Last Year)', 'validation' => ['required' => true] ],
                    [ 'id' => 'sales_2', 'type' => 'select', 'label' => 'Preferred Region', 'options' => ['North', 'South', 'East', 'West', 'Remote'], 'validation' => ['required' => true] ],
                    [ 'id' => 'sales_3', 'type' => 'textarea', 'label' => 'Pitch us a product you love in 100 words.', 'validation' => ['required' => true] ]
                ]
            ]
        );

        // 2. Assign to latest job if exists
        $job = JobPosting::latest()->first();
        if ($job) {
            $job->update(['screening_template_id' => $template->id]);
        }
    }
}
