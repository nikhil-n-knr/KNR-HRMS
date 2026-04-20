<?php

namespace App\Exports;

use App\Models\Project;
use App\Models\ProjectExtension;
use App\Models\WorkAssignment;
use App\Models\Timesheet;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\Exportable;

class ProjectExtensionGovernanceExport implements WithMultipleSheets
{
    use Exportable;

    private $project;

    public function __construct(Project $project)
    {
        $this->project = $project;
    }

    public function sheets(): array
    {
        // Load all data once — shared across sheets
        $this->project->load([
            'tasks.assignees',
            'tasks.timesheets',
            'tasks.assignments',
        ]);

        $extensions     = ProjectExtension::where('project_id', $this->project->id)
            ->with(['creator:id,name', 'task:id,title'])
            ->orderBy('created_at', 'asc')
            ->get();

        $allocatedHours = WorkAssignment::where('project_id', $this->project->id)->sum('allocated_hours');
        $actualHours    = Timesheet::where('project_id', $this->project->id)
            ->whereIn('status', ['Approved', 'approved'])
            ->sum('hours_spent');

        return [
            new \App\Exports\Sheets\ExtensionSummarySheet($this->project, (float)$allocatedHours, (float)$actualHours, $extensions),
            new \App\Exports\Sheets\ExtensionLogSheet($this->project->id),
            new \App\Exports\Sheets\ExtensionPersonPerformanceSheet($this->project, $extensions),
            new \App\Exports\Sheets\ExtensionTaskDriftSheet($this->project, $extensions),
        ];
    }
}
