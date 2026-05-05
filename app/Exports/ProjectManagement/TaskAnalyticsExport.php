<?php

namespace App\Exports\ProjectManagement;

use App\Models\Project;
use App\Models\Task;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TaskAnalyticsExport implements WithMultipleSheets
{
    protected $project;
    protected $task;
    protected $data;

    public function __construct(Project $project, Task $task, array $data)
    {
        $this->project = $project;
        $this->task = $task;
        $this->data = $data;
    }

    public function sheets(): array
    {
        return [
            new TaskOverviewSheet($this->project, $this->task, $this->data),
            new TaskContributorsSheet($this->data['contributors']),
            new TaskExtensionSheet($this->data['extensions']),
            new TaskActivitySheet($this->data['activities']),
        ];
    }
}

class TaskOverviewSheet implements FromCollection, WithHeadings, WithMapping, WithTitle, ShouldAutoSize, WithStyles
{
    protected $project;
    protected $task;
    protected $data;

    public function __construct($project, $task, $data)
    {
        $this->project = $project;
        $this->task = $task;
        $this->data = $data;
    }

    public function collection()
    {
        return collect([$this->data]);
    }

    public function title(): string
    {
        return 'Overview';
    }

    public function headings(): array
    {
        return [
            'Task ID', 'Task Title', 'Project', 'Module', 'Created At',
            'Estimated Hours', 'Matrix Planned', 'Approved Actual', 'Pending Actual',
            'Work Days (Excl. Holidays)', 'Timeline Drift (Days)', 'Effort Drift (Hours)'
        ];
    }

    public function map($row): array
    {
        return [
            $this->task->id,
            $this->task->title,
            $this->project->name,
            $this->task->module->name ?? 'N/A',
            $this->task->created_at->format('Y-m-d H:i'),
            $row['efforts']['estimated_hours'],
            $row['efforts']['matrix_planned_hours'],
            $row['efforts']['actual_approved_hours'],
            $row['efforts']['actual_pending_hours'],
            $row['efforts']['work_days'],
            $row['drift']['days'],
            $row['drift']['hours'],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '4F46E5']]],
        ];
    }
}

class TaskContributorsSheet implements FromCollection, WithHeadings, WithMapping, WithTitle, ShouldAutoSize, WithStyles
{
    protected $contributors;

    public function __construct($contributors)
    {
        $this->contributors = $contributors;
    }

    public function collection()
    {
        return collect($this->contributors);
    }

    public function title(): string
    {
        return 'Contributors';
    }

    public function headings(): array
    {
        return ['User ID', 'Name', 'Planned Hours', 'Actual Hours', 'Remaining Hours', 'Over Consumption'];
    }

    public function map($row): array
    {
        return [
            $row['id'],
            $row['name'],
            $row['planned'],
            $row['actual'],
            $row['remaining'],
            $row['over_consumption'],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '4F46E5']]],
        ];
    }
}

class TaskExtensionSheet implements FromCollection, WithHeadings, WithMapping, WithTitle, ShouldAutoSize, WithStyles
{
    protected $extensions;

    public function __construct($extensions)
    {
        $this->extensions = $extensions;
    }

    public function collection()
    {
        return collect($this->extensions);
    }

    public function title(): string
    {
        return 'Extensions';
    }

    public function headings(): array
    {
        return ['Date', 'Added By', 'Type', 'Reason', 'Days Added', 'Hours Added'];
    }

    public function map($row): array
    {
        return [
            $row->created_at->format('Y-m-d'),
            $row->creator->name ?? 'System',
            $row->type,
            $row->reason,
            $row->days_added,
            $row->hours_added,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '4F46E5']]],
        ];
    }
}

class TaskActivitySheet implements FromCollection, WithHeadings, WithMapping, WithTitle, ShouldAutoSize, WithStyles
{
    protected $activities;

    public function __construct($activities)
    {
        $this->activities = $activities;
    }

    public function collection()
    {
        return collect($this->activities);
    }

    public function title(): string
    {
        return 'Activity Log';
    }

    public function headings(): array
    {
        return ['Date/Time', 'User', 'Action', 'Description'];
    }

    public function map($row): array
    {
        return [
            $row->created_at->format('Y-m-d H:i:s'),
            $row->user->name ?? 'System',
            $row->action,
            $row->description,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '4F46E5']]],
        ];
    }
}
