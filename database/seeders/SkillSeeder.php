<?php

namespace Database\Seeders;

use App\Models\Skill;
use Illuminate\Database\Seeder;

class SkillSeeder extends Seeder
{
    public function run()
    {
        $skills = [
            // Tech
            'PHP', 'Laravel', 'Vue.js', 'React', 'Node.js', 'Python', 'Java', 'C++', 'C#', 
            'JavaScript', 'TypeScript', 'SQL', 'PostgreSQL', 'MySQL', 'MongoDB', 'AWS', 'Docker', 'Kubernetes',
            'Git', 'CI/CD', 'Rest API', 'GraphQL',
            // Design
            'Figma', 'Adobe XD', 'Photoshop', 'UI/UX',
            // Sales/Marketing
            'Sales', 'Marketing', 'SEO', 'Content Writing', 'CRM', 'Cold Calling', 'Negotiation',
            // HR/Admin
            'Recruitment', 'HR Policy', 'Payroll', 'Excel', 'Communication'
        ];

        foreach ($skills as $skill) {
            Skill::firstOrCreate(['name' => $skill]);
        }
    }
}
