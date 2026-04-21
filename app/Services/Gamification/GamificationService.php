<?php

namespace App\Services\Gamification;

use App\Models\Employee;
use App\Models\PointRule;
use App\Models\Badge;
use App\Models\EmployeePoint;
use App\Models\EmployeeBadge;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

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
            $record = $this->createPointEntry(
                $employee,
                $rule->id,
                $rule->points,
                $eventKey,
                $metadata
            );

            Log::info("Gamification: Awarded {$rule->points} points to employee #{$employee->id} for {$eventKey}");

            return $record;
        });
    }

    /**
     * Specifically award points manually (e.g. from an admin adjustment)
     */
    public function awardManualPoints(Employee $employee, int $points, string $reason, array $metadata = [])
    {
        return $this->createPointEntry(
            $employee,
            null,
            $points,
            'manual.adjustment',
            array_merge(['reason' => $reason], $metadata)
        );
    }

    private function createPointEntry(Employee $employee, ?int $ruleId, int $points, string $eventKey, array $metadata = []): ?EmployeePoint
    {
        $table = 'employee_points';

        if (!Schema::hasTable($table)) {
            Log::warning('Gamification: employee_points table missing. Skipping point award.', [
                'employee_id' => $employee->id,
                'event_key' => $eventKey,
            ]);
            return null;
        }

        $ruleColumn = Schema::hasColumn($table, 'rule_id')
            ? 'rule_id'
            : (Schema::hasColumn($table, 'point_rule_id') ? 'point_rule_id' : null);

        $pointsColumn = Schema::hasColumn($table, 'points')
            ? 'points'
            : (Schema::hasColumn($table, 'points_awarded') ? 'points_awarded' : null);

        $eventColumn = Schema::hasColumn($table, 'event_key')
            ? 'event_key'
            : (Schema::hasColumn($table, 'event_reference') ? 'event_reference' : null);

        $metaColumn = Schema::hasColumn($table, 'metadata')
            ? 'metadata'
            : (Schema::hasColumn($table, 'reason') ? 'reason' : null);

        if (!$pointsColumn) {
            Log::warning('Gamification: No points column found on employee_points. Skipping point award.', [
                'employee_id' => $employee->id,
                'event_key' => $eventKey,
            ]);
            return null;
        }

        $payload = [
            'employee_id' => $employee->id,
            $pointsColumn => $points,
        ];

        if ($ruleColumn) {
            $payload[$ruleColumn] = $ruleId;
        }

        if ($eventColumn) {
            $payload[$eventColumn] = $eventKey;
        }

        if ($metaColumn) {
            $payload[$metaColumn] = $metaColumn === 'metadata'
                ? json_encode($metadata)
                : ($metadata['reason'] ?? ('Event: ' . $eventKey));
        }

        if (Schema::hasColumn($table, 'created_at')) {
            $payload['created_at'] = now();
        }

        if (Schema::hasColumn($table, 'updated_at')) {
            $payload['updated_at'] = now();
        }

        $id = DB::table($table)->insertGetId($payload);

        return EmployeePoint::query()->find($id);
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
        $employeePointsTable = 'employee_points';

        switch ($badge->slug) {
            case 'social-star':
                // Check profile completion (simplistic example)
                return !empty($employee->avatar) && !empty($employee->email) && !empty($employee->phone);
            
            case 'early-bird':
                // Awarded for 10 on-time check-ins
                return $this->countEmployeePointEvents($employee->id, 'attendance.checkin.ontime', $employeePointsTable) >= 10;

            case 'attendance-master':
                // Awarded for Zero late marks in a full month
                // This would require checking attendance_logs for the last month
                // For now, let's keep it based on points milestones if complex data isn't ready
                return $this->countEmployeePointEvents($employee->id, 'attendance.checkin.ontime', $employeePointsTable) >= 22; // approx one month of on-time

            default:
                return false;
        }
    }

    private function countEmployeePointEvents(int $employeeId, string $eventKey, string $table = 'employee_points'): int
    {
        if (!Schema::hasTable($table)) {
            return 0;
        }

        $query = DB::table($table)->where('employee_id', $employeeId);

        if (Schema::hasColumn($table, 'event_key')) {
            $query->where('event_key', $eventKey);
        } elseif (Schema::hasColumn($table, 'event_reference')) {
            $query->where('event_reference', $eventKey);
        } else {
            return 0;
        }

        return (int) $query->count();
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
