<?php

namespace App\Services\Employee;

use App\Models\Badge;
use App\Models\Employee;
use App\Models\EmployeeBadge;
use App\Models\EmployeeStreak;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RewardsAnalyticsService
{
    public function build(Employee $employee, ?string $dateFrom = null, ?string $dateTo = null, ?string $source = null): array
    {
        $now = Carbon::now();
        $rangeStart = $this->normalizeDate($dateFrom, true) ?? $now->copy()->subDays(89)->startOfDay();
        $rangeEnd = $this->normalizeDate($dateTo, false) ?? $now->copy()->endOfDay();

        if ($rangeStart->greaterThan($rangeEnd)) {
            [$rangeStart, $rangeEnd] = [$rangeEnd->copy()->startOfDay(), $rangeStart->copy()->endOfDay()];
        }

        $pointsTable = 'employee_points';
        $hasPointsTable = Schema::hasTable($pointsTable);
        $pointsColumn = $hasPointsTable ? $this->resolvePointsColumn($pointsTable) : null;
        $eventColumn = $hasPointsTable ? $this->resolveEventColumn($pointsTable) : null;
        $ruleColumn = $hasPointsTable ? $this->resolveRuleColumn($pointsTable) : null;
        $metaColumn = $hasPointsTable ? $this->resolveMetadataColumn($pointsTable) : null;

        $lifetimePoints = 0;
        $monthlyPoints = 0;
        $rangeRows = collect();

        if ($hasPointsTable && $pointsColumn) {
            $lifetimePoints = (int) DB::table($pointsTable)
                ->where('employee_id', $employee->id)
                ->sum($pointsColumn);

            $monthlyPoints = (int) DB::table($pointsTable)
                ->where('employee_id', $employee->id)
                ->whereBetween('created_at', [$now->copy()->startOfMonth(), $now->copy()->endOfDay()])
                ->sum($pointsColumn);

            $query = DB::table($pointsTable . ' as ep')
                ->leftJoin('point_rules as pr', function ($join) use ($ruleColumn) {
                    if ($ruleColumn) {
                        $join->on('pr.id', '=', 'ep.' . $ruleColumn);
                    }
                })
                ->where('ep.employee_id', $employee->id)
                ->whereBetween('ep.created_at', [$rangeStart, $rangeEnd])
                ->orderByDesc('ep.created_at')
                ->limit(300)
                ->select([
                    'ep.id',
                    'ep.created_at',
                    'pr.name as rule_name',
                    'pr.event_category as rule_category',
                    $this->safeSelect('ep', $pointsColumn, 'points_value'),
                    $eventColumn ? $this->safeSelect('ep', $eventColumn, 'event_value') : DB::raw("'' as event_value"),
                    $metaColumn ? $this->safeSelect('ep', $metaColumn, 'meta_value') : DB::raw("'' as meta_value"),
                ]);

            $rangeRows = collect($query->get())->map(function ($row) {
                $eventKey = (string) ($row->event_value ?? '');
                $source = $this->sourceFromEventKey($eventKey, (string) ($row->rule_category ?? ''));

                return [
                    'id' => (int) $row->id,
                    'created_at' => $row->created_at,
                    'event_key' => $eventKey,
                    'source' => $source,
                    'rule_name' => $row->rule_name,
                    'points' => (int) ($row->points_value ?? 0),
                    'meta' => $this->normalizeMeta($row->meta_value ?? null),
                ];
            });

            if ($source && $source !== 'all') {
                $rangeRows = $rangeRows->filter(fn ($row) => $row['source'] === $source)->values();
            }
        }

        $sourceTotals = $rangeRows
            ->groupBy('source')
            ->map(fn (Collection $rows) => (int) $rows->sum('points'))
            ->sortDesc();

        $pointsTrend = $this->buildPointsTrend($rangeRows, $rangeStart, $rangeEnd);

        $scrum = $this->buildScrumStats($employee->id, $rangeStart, $rangeEnd, $now);

        $allBadges = Badge::query()
            ->when(Schema::hasColumn('badges', 'is_active'), fn ($q) => $q->where('is_active', true))
            ->orderBy('name')
            ->get();

        $employeeBadges = EmployeeBadge::with('badge')
            ->where('employee_id', $employee->id)
            ->orderByDesc('awarded_at')
            ->orderByDesc('created_at')
            ->get();

        $earnedBadgeIds = $employeeBadges->pluck('badge_id')->all();
        $badges = $this->buildBadgeCollections($employee, $allBadges, $employeeBadges, $earnedBadgeIds, $rangeRows);

        $streakRows = Schema::hasTable('employee_streaks')
            ? EmployeeStreak::where('employee_id', $employee->id)->get()
            : collect();

        $currentStreak = (int) $streakRows->max('current_streak');
        $bestStreak = (int) $streakRows->max('max_streak');
        $lastBadge = $employeeBadges->first();

        return [
            'filters' => [
                'date_from' => $rangeStart->toDateString(),
                'date_to' => $rangeEnd->toDateString(),
                'source' => $source ?: 'all',
            ],
            'summary' => [
                'lifetime_points' => $lifetimePoints,
                'points_this_month' => $monthlyPoints,
                'scrum_points_this_month' => $scrum['month_total'],
                'badges_earned' => count($earnedBadgeIds),
                'current_streak' => $currentStreak,
                'best_streak' => $bestStreak,
                'last_badge' => $lastBadge ? [
                    'name' => $lastBadge->badge?->name,
                    'icon' => $lastBadge->badge?->icon ?: '🏆',
                    'awarded_at' => $lastBadge->awarded_at ?: $lastBadge->created_at,
                ] : null,
            ],
            'charts' => [
                'points_trend' => $pointsTrend,
                'source_distribution' => [
                    'labels' => $sourceTotals->keys()->values(),
                    'data' => $sourceTotals->values(),
                ],
                'scrum_trend' => $scrum['trend'],
                'badge_timeline' => $employeeBadges->take(20)->map(function ($row) {
                    return [
                        'date' => optional($row->awarded_at ?: $row->created_at)?->toDateString(),
                        'name' => $row->badge?->name ?? 'Badge',
                        'icon' => $row->badge?->icon ?: '🏆',
                    ];
                })->values(),
            ],
            'badges' => $badges,
            'ledger' => [
                'data' => $rangeRows->take(120)->map(function ($row) {
                    return [
                        'id' => $row['id'],
                        'when' => $row['created_at'],
                        'event_key' => $row['event_key'],
                        'rule_name' => $row['rule_name'],
                        'source' => $row['source'],
                        'points' => $row['points'],
                        'reason' => $this->metaReason($row['meta']),
                    ];
                })->values(),
            ],
        ];
    }

    private function buildPointsTrend(Collection $rows, Carbon $start, Carbon $end): array
    {
        $byDate = $rows
            ->groupBy(function ($row) {
                return Carbon::parse($row['created_at'])->toDateString();
            })
            ->map(fn (Collection $group) => (int) $group->sum('points'));

        $labels = [];
        $data = [];

        $cursor = $start->copy();
        while ($cursor->lte($end)) {
            $dateKey = $cursor->toDateString();
            $labels[] = $cursor->format('d M');
            $data[] = (int) ($byDate[$dateKey] ?? 0);
            $cursor->addDay();
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }

    private function buildScrumStats(int $employeeId, Carbon $start, Carbon $end, Carbon $now): array
    {
        $labels = [];
        $data = [];
        $cursor = $start->copy()->startOfWeek();
        while ($cursor->lte($end)) {
            $labels[] = 'W/O ' . $cursor->format('d M');
            $data[] = 0;
            $cursor->addWeek();
        }

        if (!Schema::hasTable('work_assignments')) {
            return [
                'total' => 0,
                'month_total' => 0,
                'trend' => [
                    'labels' => $labels,
                    'data' => $data,
                ],
            ];
        }

        $taskTable = Schema::hasTable('project_tasks') ? 'project_tasks' : (Schema::hasTable('tasks') ? 'tasks' : null);
        if (!$taskTable) {
            return [
                'total' => 0,
                'month_total' => 0,
                'trend' => [
                    'labels' => $labels,
                    'data' => $data,
                ],
            ];
        }

        $hasDueDate = Schema::hasColumn($taskTable, 'due_date');
        $hasScrumPoints = Schema::hasColumn($taskTable, 'scrum_points');
        if (!$hasDueDate || !$hasScrumPoints) {
            return [
                'total' => 0,
                'month_total' => 0,
                'trend' => [
                    'labels' => $labels,
                    'data' => $data,
                ],
            ];
        }

        $baseQuery = fn () => DB::table('work_assignments as wa')
            ->leftJoin($taskTable . ' as t', 't.id', '=', 'wa.task_id')
            ->where('wa.assignee_type', Employee::class)
            ->where('wa.assignee_id', $employeeId)
            ->whereNotNull('t.id')
            ->whereNotNull('t.scrum_points')
            ->whereNotNull('t.due_date');

        $rows = $baseQuery()
            ->whereBetween('t.due_date', [$start->toDateString(), $end->toDateString()])
            ->select(['t.id', 't.due_date', 't.scrum_points'])
            ->get();

        $total = (int) collect($rows)->sum(fn ($row) => (int) ($row->scrum_points ?? 0));

        $monthRows = (int) $baseQuery()
            ->whereBetween('t.due_date', [$now->copy()->startOfMonth()->toDateString(), $now->copy()->toDateString()])
            ->sum('t.scrum_points');

        $weekMap = collect($rows)->groupBy(function ($row) {
            $weekStart = Carbon::parse($row->due_date)->startOfWeek();
            return $weekStart->toDateString();
        })->map(fn (Collection $group) => (int) $group->sum(fn ($r) => (int) ($r->scrum_points ?? 0)));

        $labels = [];
        $data = [];
        $cursor = $start->copy()->startOfWeek();
        while ($cursor->lte($end)) {
            $key = $cursor->toDateString();
            $labels[] = 'W/O ' . $cursor->format('d M');
            $data[] = (int) ($weekMap[$key] ?? 0);
            $cursor->addWeek();
        }

        return [
            'total' => $total,
            'month_total' => (int) $monthRows,
            'trend' => [
                'labels' => $labels,
                'data' => $data,
            ],
        ];
    }

    private function buildBadgeCollections(Employee $employee, Collection $allBadges, Collection $employeeBadges, array $earnedBadgeIds, Collection $pointsRows): array
    {
        $earned = $employeeBadges->map(function ($row) {
            $badge = $row->badge;
            return [
                'id' => $badge?->id,
                'name' => $badge?->name ?? 'Badge',
                'slug' => $badge?->slug,
                'icon' => $badge?->icon ?: '🏆',
                'description' => $this->badgeDescription($badge),
                'points_bonus' => (int) ($badge?->points_bonus ?? 0),
                'awarded_at' => optional($row->awarded_at ?: $row->created_at)?->toDateString(),
            ];
        })->values();

        $inProgress = collect();
        $locked = collect();

        foreach ($allBadges as $badge) {
            if (in_array($badge->id, $earnedBadgeIds, true)) {
                continue;
            }

            $progress = $this->badgeProgress($employee, $badge, $pointsRows);
            $item = [
                'id' => $badge->id,
                'name' => $badge->name,
                'slug' => $badge->slug,
                'icon' => $badge->icon ?: '🏆',
                'description' => $this->badgeDescription($badge),
                'points_bonus' => (int) ($badge->points_bonus ?? 0),
                'progress' => $progress,
            ];

            if ($progress['target'] > 0 && $progress['current'] > 0) {
                $inProgress->push($item);
            } else {
                $locked->push($item);
            }
        }

        return [
            'earned' => $earned,
            'in_progress' => $inProgress->values(),
            'locked' => $locked->values(),
        ];
    }

    private function badgeProgress(Employee $employee, Badge $badge, Collection $pointsRows): array
    {
        $slug = (string) ($badge->slug ?? '');
        $default = ['current' => 0, 'target' => 0, 'percent' => 0];

        if ($slug === 'early-bird') {
            $target = 10;
            $current = $pointsRows->where('event_key', 'attendance.checkin.ontime')->count();
            return $this->progress($current, $target);
        }

        if ($slug === 'attendance-master') {
            $target = 22;
            $current = $pointsRows->where('event_key', 'attendance.checkin.ontime')->count();
            return $this->progress($current, $target);
        }

        if ($slug === 'social-star') {
            $fields = [
                !empty($employee->avatar),
                !empty($employee->email),
                !empty($employee->phone),
                !empty($employee->address),
                !empty($employee->date_of_birth),
            ];
            $current = count(array_filter($fields));
            return $this->progress($current, count($fields));
        }

        return $default;
    }

    private function progress(int $current, int $target): array
    {
        $safeTarget = max(1, $target);
        return [
            'current' => $current,
            'target' => $target,
            'percent' => (int) max(0, min(100, round(($current / $safeTarget) * 100))),
        ];
    }

    private function sourceFromEventKey(string $eventKey, string $ruleCategory = ''): string
    {
        $normalized = strtolower($eventKey);
        $category = strtolower($ruleCategory);

        if (str_starts_with($normalized, 'attendance.') || $category === 'attendance') {
            return 'attendance';
        }

        if (str_starts_with($normalized, 'scrum.') || str_starts_with($normalized, 'sprint.') || str_starts_with($normalized, 'task.') || $category === 'scrum') {
            return 'scrum';
        }

        if (str_starts_with($normalized, 'performance.') || $category === 'performance') {
            return 'performance';
        }

        if (str_starts_with($normalized, 'training.') || $category === 'learning') {
            return 'learning';
        }

        if (str_starts_with($normalized, 'manual.')) {
            return 'manual';
        }

        return 'other';
    }

    private function badgeDescription(?Badge $badge): string
    {
        if (!$badge) {
            return 'Achievement unlocked by consistent performance.';
        }

        if (!empty($badge->criteria_description)) {
            return (string) $badge->criteria_description;
        }

        if (property_exists($badge, 'description') && !empty($badge->description)) {
            return (string) $badge->description;
        }

        return 'Achievement unlocked by consistent performance.';
    }

    private function resolvePointsColumn(string $table): ?string
    {
        if (Schema::hasColumn($table, 'points')) {
            return 'points';
        }
        if (Schema::hasColumn($table, 'points_awarded')) {
            return 'points_awarded';
        }

        return null;
    }

    private function resolveEventColumn(string $table): ?string
    {
        if (Schema::hasColumn($table, 'event_key')) {
            return 'event_key';
        }
        if (Schema::hasColumn($table, 'event_reference')) {
            return 'event_reference';
        }

        return null;
    }

    private function resolveRuleColumn(string $table): ?string
    {
        if (Schema::hasColumn($table, 'rule_id')) {
            return 'rule_id';
        }
        if (Schema::hasColumn($table, 'point_rule_id')) {
            return 'point_rule_id';
        }

        return null;
    }

    private function resolveMetadataColumn(string $table): ?string
    {
        if (Schema::hasColumn($table, 'metadata')) {
            return 'metadata';
        }
        if (Schema::hasColumn($table, 'reason')) {
            return 'reason';
        }

        return null;
    }

    private function safeSelect(string $tableAlias, string $column, string $as)
    {
        return DB::raw($tableAlias . '.' . $column . ' as ' . $as);
    }

    private function normalizeMeta($value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (is_string($value) && $value !== '') {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }

            return ['reason' => $value];
        }

        return [];
    }

    private function metaReason(array $meta): ?string
    {
        if (!empty($meta['reason'])) {
            return (string) $meta['reason'];
        }
        if (!empty($meta['note'])) {
            return (string) $meta['note'];
        }

        return null;
    }

    private function normalizeDate(?string $value, bool $startOfDay): ?Carbon
    {
        if (!$value) {
            return null;
        }

        try {
            $date = Carbon::parse($value);
            return $startOfDay ? $date->startOfDay() : $date->endOfDay();
        } catch (\Throwable) {
            return null;
        }
    }
}
