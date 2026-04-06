<?php

namespace Database\Seeders;

use App\Models\CRM\Achievement;
use Illuminate\Database\Seeder;

class CRMAchievementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $achievements = [
            [
                'name' => 'The Closer',
                'slug' => 'the-closer',
                'description' => 'Won 5 deals in a single week.',
                'icon' => 'fas fa-handshake',
                'points_reward' => 500,
                'requirement_type' => 'deals_won',
                'requirement_value' => 5,
            ],
            [
                'name' => 'Revenue Rockstar',
                'slug' => 'revenue-rockstar',
                'description' => 'Generated over $100,000 in total revenue.',
                'icon' => 'fas fa-star',
                'points_reward' => 1000,
                'requirement_type' => 'revenue',
                'requirement_value' => 100000,
            ],
            [
                'name' => 'Activity Machine',
                'slug' => 'activity-machine',
                'description' => 'Logged 50 activities (calls, emails, meetings).',
                'icon' => 'fas fa-cog',
                'points_reward' => 250,
                'requirement_type' => 'activities',
                'requirement_value' => 50,
            ],
            [
                'name' => 'Elite Performer',
                'slug' => 'elite-performer',
                'description' => 'Reached 5,000 total gamification points.',
                'icon' => 'fas fa-medal',
                'points_reward' => 2000,
                'requirement_type' => 'points',
                'requirement_value' => 5000,
            ],
        ];

        foreach ($achievements as $achievement) {
            Achievement::updateOrCreate(['slug' => $achievement['slug']], $achievement);
        }
    }
}
