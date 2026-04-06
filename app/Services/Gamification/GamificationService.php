<?php

namespace App\Services\Gamification;

use App\Models\Employee;
use App\Models\PointRule;
use App\Models\Badge;
use App\Models\EmployeePoint;
use App\Models\EmployeeBadge;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GamificationService
{
    /**
     * Award points to an employee based on an event trigger.
     */
    public function awardPoints(Employee $employee, string $eventKey, array $metadata = [])
    {
        $rule = PointRule::where('event_key', $eventKey)->where('is_active', true)->first();

        if (!$rule) {
            Log::info("Gamification: No active rule found for event key: {$eventKey}");
            return null;
        }

        return DB::transaction(function () use ($employee, $rule, $eventKey, $metadata) {
            $record = EmployeePoint::create([
                'employee_id' => $employee->id,
                'rule_id' => $rule->id,
                'points' => $rule->points,
                'event_key' => $eventKey,
                'metadata' => $metadata
            ]);

            Log::info("Gamification: Awarded {$rule->points} points to employee #{$employee->id} for {$eventKey}");

            return $record;
        });
    }

    /**
     * Specifically award points manually (e.g. from an admin adjustment)
     */
    public function awardManualPoints(Employee $employee, int $points, string $reason, array $metadata = [])
    {
        return EmployeePoint::create([
            'employee_id' => $employee->id,
            'rule_id' => null,
            'points' => $points,
            'event_key' => 'manual.adjustment',
            'metadata' => array_merge(['reason' => $reason], $metadata)
        ]);
    }

    /**
     * Check and award badges based on employee milestones.
     * This can be called after point awards or periodically.
     */
    public function checkBadges(Employee $employee)
    {
        $allBadges = Badge::where('is_active', true)->get();
        $awarded = [];

        foreach ($allBadges as $badge) {
            // Check if already awarded
            $exists = EmployeeBadge::where('employee_id', $employee->id)
                ->where('badge_id', $badge->id)
                ->exists();

            if ($exists) continue;

            if ($this->shouldAwardBadge($employee, $badge)) {
                $awarded[] = $this->awardBadge($employee, $badge);
            }
        }

        return $awarded;
    }

    /**
     * Logic for badge criteria validation.
     */
    protected function shouldAwardBadge(Employee $employee, Badge $badge)
    {
        switch ($badge->slug) {
            case 'social-star':
                // Check profile completion (simplistic example)
                return !empty($employee->avatar) && !empty($employee->email) && !empty($employee->phone);
            
            case 'early-bird':
                // Awarded for 10 on-time check-ins
                return $employee->points()
                    ->where('event_key', 'attendance.checkin.ontime')
                    ->count() >= 10;

            case 'attendance-master':
                // Awarded for Zero late marks in a full month
                // This would require checking attendance_logs for the last month
                // For now, let's keep it based on points milestones if complex data isn't ready
                return $employee->points()
                    ->where('event_key', 'attendance.checkin.ontime')
                    ->count() >= 22; // approx one month of on-time

            default:
                return false;
        }
    }

    protected function awardBadge(Employee $employee, Badge $badge)
    {
        return DB::transaction(function () use ($employee, $badge) {
            $record = EmployeeBadge::create([
                'employee_id' => $employee->id,
                'badge_id' => $badge->id,
                'awarded_at' => now()
            ]);

            // Also award the bonus points associated with the badge
            $this->awardManualPoints(
                $employee, 
                $badge->points_bonus, 
                "Badge Reward: {$badge->name}",
                ['badge_id' => $badge->id]
            );

            Log::info("Gamification: Awarded badge '{$badge->name}' to employee #{$employee->id}");

            return $record;
        });
    }
}
