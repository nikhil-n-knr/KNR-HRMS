<?php

namespace App\Exports\Sheets;

use App\Models\Task;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class VelocityAnalysisSheet implements FromCollection, WithTitle, WithHeadings, WithMapping
{
    private $projectId;

    public function __construct($projectId)
    {
        $this->projectId = $projectId;
    }

    public function collection()
    {
        // Fetch all non-deleted tasks for the project
        return Task::where('project_id', $this->projectId)
            ->with(['assignees', 'timesheets'])
            ->orderBy('due_date', 'asc')
            ->get();
    }

    public function title(): string
    {
        return 'Execution Velocity';
    }

    public function headings(): array
    {
        return [
            'Task Title',
            'Status',
            'Assignees',
            'Original Estimate (hrs)',
            'Actual Invested (hrs)',
            'Variance (Net Eff)',
            'Due Date',
            'Completion Date',
            'Delay Status',
            'Performance Index'
        ];
    }

    public function map($task): array
    {
        $actual = $task->timesheets()->sum('hours');
        $variance = $task->estimated_hours - $actual;
        
        // Completion Date: use updated_at if status is 'done' / 'completed'
        $completionDate = ($task->status === 'completed' || $task->status === 'done' || $task->status === 'Done') 
                        ? $task->updated_at->format('Y-m-d') 
                        : 'PENDING';
        
        $delayStatus = 'On Track';
        if ($completionDate !== 'PENDING') {
            if ($task->updated_at->gt($task->due_date)) {
                $days = $task->due_date->diffInDays($task->updated_at);
                $delayStatus = "DELAYED ({$days} days)";
            } else {
                $delayStatus = "DELIVERED EARLY";
            }
        } elseif ($task->due_date->lt(now())) {
            $delayStatus = "OVERDUE";
        }

        $performanceIndex = $task->estimated_hours > 0 ? (round(($actual / $task->estimated_hours), 2) * 100) . '%' : 'N/A';

        return [
            $task->title,
            strtoupper($task->status),
            $task->assignees->pluck('name')->implode(', '),
            $task->estimated_hours,
            $actual,
            $variance,
            $task->due_date->format('Y-m-d'),
            $completionDate,
            $delayStatus,
            $performanceIndex
        ];
    }
}
