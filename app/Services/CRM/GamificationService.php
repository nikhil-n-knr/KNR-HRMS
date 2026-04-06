<?php

namespace App\Services\CRM;

use App\Models\CRM\Achievement;
use App\Models\CRM\UserAchievement;
use App\Models\CRM\UserStat;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class GamificationService
{
    public function awardPoints(User $user, int $points, string $type, $amount = 0)
    {
        DB::transaction(function () use ($user, $points, $type, $amount) {
            $stats = UserStat::firstOrCreate(
                ['user_id' => $user->id, 'tenant_id' => $user->tenant_id],
                ['total_points' => 0]
            );

            $stats->total_points += $points;

            if ($type === 'deal_won') {
                $stats->deals_won_count += 1;
                $stats->total_revenue += $amount;
            } elseif ($type === 'activity_logged') {
                $stats->activities_count += 1;
            }

            $stats->save();

            $this->checkAchievements($user, $stats);
        });
    }

    protected function checkAchievements(User $user, UserStat $stats)
    {
        $potentialAchievements = Achievement::whereNotIn('id', function ($query) use ($user) {
            $query->select('achievement_id')
                  ->from('crm_user_achievements')
                  ->where('user_id', $user->id);
        })->get();

        foreach ($potentialAchievements as $achievement) {
            $earned = false;

            switch ($achievement->requirement_type) {
                case 'deals_won':
                    if ($stats->deals_won_count >= $achievement->requirement_value) {
                        $earned = true;
                    }
                    break;
                case 'revenue':
                    if ($stats->total_revenue >= $achievement->requirement_value) {
                        $earned = true;
                    }
                    break;
                case 'activities':
                    if ($stats->activities_count >= $achievement->requirement_value) {
                        $earned = true;
                    }
                    break;
                case 'points':
                    if ($stats->total_points >= $achievement->requirement_value) {
                        $earned = true;
                    }
                    break;
            }

            if ($earned) {
                UserAchievement::create([
                    'user_id' => $user->id,
                    'achievement_id' => $achievement->id,
                    'earned_at' => Carbon::now()
                ]);

                // Award points for achievement itself
                if ($achievement->points_reward > 0) {
                    $stats->total_points += $achievement->points_reward;
                    $stats->save();
                }
                
                // Fire live notification event here in future
            }
        }
    }
}
