<?php

namespace App\Services\Attendance;

use App\Models\Employee;
use App\Models\FloatingHolidayRequest;
use App\Models\Holiday;
use App\Models\Shift;
use Carbon\Carbon;

class WorkingDayResolverService
{
    /**
     * Resolve effective work days from shift config.
     * Returns map: mon..sun => bool (true means working day).
     */
    public function getWorkDayMap(?Employee $employee = null, ?Carbon $date = null): array
    {
        $date = $date ?? now();
        $shift = $this->resolveShift($employee, $date);

        $workDays = [
            'mon' => true,
            'tue' => true,
            'wed' => true,
            'thu' => true,
            'fri' => true,
            'sat' => false,
            'sun' => false,
        ];

        if ($shift && !empty($shift->work_days)) {
            $configuredDays = is_array($shift->work_days)
                ? $shift->work_days
                : json_decode((string) $shift->work_days, true);

            if (is_array($configuredDays)) {
                foreach (array_keys($workDays) as $day) {
                    $workDays[$day] = in_array(ucfirst($day), $configuredDays, true);
                }
            }
        }

        return $workDays;
    }

    public function isWeekOff(Carbon $date, ?Employee $employee = null): bool
    {
        $shift = $this->resolveShift($employee, $date);
        $workDays = $this->buildWorkDayMapFromShift($shift);
        $weekOffRules = $this->parseWeekOffRules($shift);
        $dayKey = strtolower($date->format('D')); // mon, tue...
        $baseWeekOff = isset($workDays[$dayKey]) ? $workDays[$dayKey] === false : $date->isWeekend();

        if ($this->matchesNthWeekRule($date, $weekOffRules)) {
            return true;
        }

        return $baseWeekOff;
    }

    public function getWeekOffRules(?Employee $employee = null, ?Carbon $date = null): array
    {
        $date = $date ?? now();
        $shift = $this->resolveShift($employee, $date);

        return $this->parseWeekOffRules($shift);
    }

    public function isHoliday(Carbon $date, ?Employee $employee = null): bool
    {
        $holiday = Holiday::whereDate('date', $date->toDateString())->first();

        if (!$holiday) {
            return false;
        }

        if (in_array($holiday->type, ['Fixed', 'AdHoc'], true)) {
            return true;
        }

        if ($holiday->type === 'Restricted' && $employee && $employee->user_id) {
            return FloatingHolidayRequest::where('user_id', $employee->user_id)
                ->where('holiday_id', $holiday->id)
                ->whereRaw('LOWER(status) = ?', ['approved'])
                ->exists();
        }

        return false;
    }

    public function isNonWorkingDay(Carbon $date, ?Employee $employee = null): bool
    {
        return $this->isHoliday($date, $employee) || $this->isWeekOff($date, $employee);
    }

    private function resolveShift(?Employee $employee, Carbon $date): ?Shift
    {
        if ($employee) {
            try {
                return app(AttendanceRegistryService::class)->getShiftForDate($employee, $date);
            } catch (\Throwable $e) {
                // Fallbacks below keep day resolution available even if attendance stack is unavailable.
            }
        }

        return Shift::where('is_default', true)->first() ?? Shift::first();
    }

    private function buildWorkDayMapFromShift(?Shift $shift): array
    {
        $workDays = [
            'mon' => true,
            'tue' => true,
            'wed' => true,
            'thu' => true,
            'fri' => true,
            'sat' => false,
            'sun' => false,
        ];

        if (!$shift || empty($shift->work_days)) {
            return $workDays;
        }

        $configuredDays = is_array($shift->work_days)
            ? $shift->work_days
            : json_decode((string) $shift->work_days, true);

        if (!is_array($configuredDays)) {
            return $workDays;
        }

        foreach (array_keys($workDays) as $day) {
            $workDays[$day] = in_array(ucfirst($day), $configuredDays, true);
        }

        return $workDays;
    }

    private function parseWeekOffRules(?Shift $shift): array
    {
        if (!$shift || empty($shift->week_off_rules)) {
            return [];
        }

        $rules = is_array($shift->week_off_rules)
            ? $shift->week_off_rules
            : json_decode((string) $shift->week_off_rules, true);

        if (!is_array($rules)) {
            return [];
        }

        return collect($rules)
            ->map(function ($rule) {
                $weekday = $rule['weekday'] ?? null;
                $weeks = $rule['weeks'] ?? [];

                if (!$weekday || !is_array($weeks)) {
                    return null;
                }

                return [
                    'weekday' => ucfirst(strtolower(substr((string) $weekday, 0, 3))),
                    'weeks' => collect($weeks)
                        ->map(fn($w) => strtolower((string) $w))
                        ->filter(fn($w) => in_array($w, ['1', '2', '3', '4', 'last'], true))
                        ->values()
                        ->all(),
                ];
            })
            ->filter(fn($rule) => $rule && !empty($rule['weeks']))
            ->values()
            ->all();
    }

    private function matchesNthWeekRule(Carbon $date, array $rules): bool
    {
        if (empty($rules)) {
            return false;
        }

        $dateWeekday = $date->format('D'); // Mon, Tue...
        $weekOfMonth = (string) ceil($date->day / 7);
        $isLastOccurrence = $date->copy()->addWeek()->month !== $date->month;

        foreach ($rules as $rule) {
            if (($rule['weekday'] ?? null) !== $dateWeekday) {
                continue;
            }

            $weeks = $rule['weeks'] ?? [];
            if (in_array($weekOfMonth, $weeks, true)) {
                return true;
            }

            if ($isLastOccurrence && in_array('last', $weeks, true)) {
                return true;
            }
        }

        return false;
    }
}
