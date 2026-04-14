<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ProjectComprehensiveExport implements WithMultipleSheets
{
    use Exportable;

    protected $assignments;
    protected $holidays;
    protected $startDate;
    protected $endDate;
    protected $projectId;
    protected $stats;

    /**
     * @param \Illuminate\Support\Collection $assignments
     * @param \Illuminate\Support\Collection $holidays
     * @param \Carbon\Carbon $startDate
     * @param \Carbon\Carbon $endDate
     * @param array $stats
     * @param int|null $projectId
     */
    public function __construct($assignments, $holidays, $startDate, $endDate, $stats, $projectId = null)
    {
        $this->assignments = $assignments;
        $this->holidays = $holidays;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->stats = $stats;
        $this->projectId = $projectId;
    }

    /**
     * @return array
     */
    public function sheets(): array
    {
        $sheets = [];

        // Sheet 1: High-level metrics
        $sheets[] = new Sheets\ProjectOverviewSheet($this->stats, $this->startDate, $this->endDate, $this->projectId);

        // Sheet 2: Detailed task and resource assignments
        $sheets[] = new Sheets\TaskResourceUtilizationSheet($this->assignments, $this->holidays, $this->startDate, $this->endDate);

        // Sheet 3: Recent Activity (Audit logs, changes)
        $sheets[] = new Sheets\RecentActivitiesSheet($this->projectId, $this->startDate, $this->endDate);

        // Sheet 4: Team Performance / Leaderboard
        $sheets[] = new Sheets\TeamPerformanceSheet($this->assignments, $this->startDate, $this->endDate);

        return $sheets;
    }
}
