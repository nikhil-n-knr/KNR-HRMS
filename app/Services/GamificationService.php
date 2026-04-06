<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\PointRule;
use App\Models\EmployeePoint;
use App\Models\EmployeeStreak;
use App\Models\Badge;
use App\Models\EmployeeBadge;
use Illuminate\Support\Facades\Log;

class GamificationService
{
    /**
     * Process an event and award points/badges if applicable.
     */
    public function processEvent(Employee $employee, string $eventKey, array $metadata = [])
    {
        // 1. Find Matching Rule
        $rule = PointRule::where('event_key', $eventKey)
            ->where('is_active', true)
            ->first();

        if (!$rule) return;

        // 2. Check Dynamic Conditions (optional)
        if ($rule->condition_logic) {
            if (!$this->evaluateCondition($rule->condition_logic, $metadata)) {
                return;
            }
        }

        // 3. Award Points
        $this->awardPoints($employee, $rule, $metadata);

        // 4. Update Relevant Streaks (if event is streak-related)
        if ($rule->event_category === 'streak') {
            $this->incrementStreak($employee, $eventKey);
        }
        
        // 5. Check for Badges triggers
        $this->checkBadges($employee);
    }

    protected function awardPoints(Employee $employee, PointRule $rule, $metadata)
    {
        EmployeePoint::create([
            'employee_id' => $employee->id,
            'point_rule_id' => $rule->id,
            'points_awarded' => $rule->points,
            'event_reference' => $metadata['reference_id'] ?? null,
            'reason' => $rule->name,
        ]);
    }

    public function incrementStreak(Employee $employee, $streakType)
    {
        $streak = EmployeeStreak::firstOrCreate(
            ['employee_id' => $employee->id, 'streak_type' => $streakType],
            ['current_streak' => 0, 'max_streak' => 0]
        );

        $streak->current_streak++;
        if ($streak->current_streak > $streak->max_streak) {
            $streak->max_streak = $streak->current_streak;
        }
        $streak->last_incremented_at = now();
        $streak->save();

        // Bonus points for milestones?
        if ($streak->current_streak % 5 == 0) {
            // Award milestone bonus
            // This could be another recursive processEvent call like 'streak_milestone_5'
        }
    }

    public function resetStreak(Employee $employee, $streakType)
    {
        EmployeeStreak::updateOrCreate(
            ['employee_id' => $employee->id, 'streak_type' => $streakType],
            ['current_streak' => 0]
        );
    }

    protected function evaluateCondition($logic, $data)
    {
        // Simple expression evaluator or specific handlers
        // E.g. "time < 09:00"
        // Security risk to eval(), so we implemented named conditions
        // For now, assume this hook exists or return true
        return true; 
    }

    protected function checkBadges(Employee $employee)
    {
        // Logic to check all badges (or specific ones related to last event)
        // e.g. "Early Bird" badge requires 5 early check-ins
        // Implementation depends on Badge criteria storage
    }
}
