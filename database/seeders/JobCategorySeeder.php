<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\JobCategory;

class JobCategorySeeder extends Seeder
{
    public function run()
    {
        $categories = [
            'Engineering' => ['Backend', 'Frontend', 'Full Stack', 'DevOps', 'Mobile'],
            'Human Resources' => ['Recruitment', 'Operations', 'L&D'],
            'Sales & Marketing' => ['Sales Executive', 'Digital Marketing', 'Content'],
            'Finance' => ['Accounting', 'Audit'],
            'Operations' => ['Admin', 'Support']
        ];

        foreach ($categories as $parentName => $subCategories) {
            $parent = JobCategory::firstOrCreate(['name' => $parentName], ['status' => true]);
            
            foreach ($subCategories as $subName) {
                JobCategory::firstOrCreate(
                    ['name' => $subName, 'parent_id' => $parent->id],
                    ['status' => true]
                );
            }
        }
    }
}
