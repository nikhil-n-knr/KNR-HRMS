<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\JobPosting;
use App\Models\Department;
use App\Models\Location;
use App\Models\JobCategory;
use App\Models\User;
use Faker\Factory as Faker;

class JobSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();
        
        $departments = Department::pluck('id')->toArray();
        $locations = Location::pluck('id')->toArray();
        $categories = JobCategory::whereNotNull('parent_id')->pluck('id')->toArray(); // Subcategories preferrably
        // If no subcategories, use main
        if (empty($categories)) $categories = JobCategory::pluck('id')->toArray();
        
        $userId = User::first()->id ?? 1;

        $titles = [
            'Frontend Developer', 'Backend Engineer', 'Full Stack Developer', 'Product Manager', 'UX Designer',
            'HR Business Partner', 'Sales Executive', 'Customer Success Manager', 'DevOps Engineer', 'QA Tester',
            'Data Analyst', 'Marketing Specialist', 'Content Writer', 'Accountant', 'Office Admin',
            'Mobile App Developer', 'AI Researcher', 'Machine Learning Engineer', 'Cybersecurity Specialist', 'Network Engineer'
        ];

        for ($i = 0; $i < 25; $i++) {
            $seq = str_pad($i + 1, 3, '0', STR_PAD_LEFT);
            
            JobPosting::updateOrCreate(
                ['job_code' => "JOB-" . date('Y') . "-Seed{$seq}"],
                [
                    'title' => $faker->randomElement($titles),
                    'department_id' => $faker->randomElement($departments),
                    'location_id' => $faker->randomElement($locations),
                    'job_category_id' => $faker->randomElement($categories),
                    'type' => $faker->randomElement(['Full-time', 'Part-time', 'Contract', 'Remote']),
                    'description' => $faker->paragraph(4),
                    'min_experience' => $faker->numberBetween(0, 5),
                    'max_experience' => $faker->numberBetween(6, 12),
                    'salary_min' => $faker->numberBetween(4, 10) * 100000,
                    'salary_max' => $faker->numberBetween(12, 30) * 100000,
                    'salary_currency' => 'INR',
                    'status' => $faker->randomElement(['Published', 'Draft']),
                    'valid_through' => $faker->dateTimeBetween('now', '+3 months'),
                    'created_by' => $userId,
                    'notification_config' => ['loops' => [$userId]],
                ]
            );
        }
    }
}
