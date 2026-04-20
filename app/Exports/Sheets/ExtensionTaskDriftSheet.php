<?php

namespace App\Exports\Sheets;

use App\Models\Project;
use App\Models\WorkAssignment;
use App\Models\Timesheet;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;

class ExtensionTaskDriftSheet implements FromArray, WithTitle, WithStyles, WithColumnWidths
{
    private $project;
    private $extensions;

    public function __construct(Project $project, $extensions)
    {
        $this->project    = $project;
        $this->extensions = $extensions;
    }

    public function title(): string { return 'Task Drift Detail'; }

    public function array(): array
    {
        $rows = [
            ['Task-Level Drift Report — Project: ' . $this->project->name],
            ['Generated: ' . now()->format('d M Y H:i')],
            [],
            ['Task', 'Planned Start', 'Planned End', 'Actual Last Entry', 'Timeline Status',
             'Planned Hours', 'Allocated Hours', 'Actual Hours', 'Extended Hours', 'Effort Status',
             'Extensions Count', 'Extension Categories'],
        ];

        $tasks = $this->project->tasks()
            ->with([
                'assignments',
                'timesheets' => fn($q) => $q->whereIn('status', ['Approved', 'approved']),
            ])
            ->get();

        foreach ($tasks as $task) {
            $plannedHours   = (float)$task->estimated_hours;
            $allocatedHours = (float)$task->assignments->sum('allocated_hours');
            $actualHours    = (float)$task->timesheets->sum('hours_spent');
            $lastTs         = $task->timesheets->max('date');
            $taskExtensions = $this->extensions->where('task_id', $task->id);
            $extHours       = $taskExtensions->sum('hours_added');
            $extCount       = $taskExtensions->count();

            // Timeline status
            $timelineStatus = 'On Track';
            if ($task->due_date && $lastTs) {
                $diff = Carbon::parse($task->due_date)->diffInDays(Carbon::parse($lastTs), false);
                $timelineStatus = $diff > 0 ? "Late by {$diff}d" : ($diff < 0 ? "Early by " . abs($diff) . "d" : "On Time");
            } elseif ($task->due_date && Carbon::parse($task->due_date)->isPast() && $task->status !== 'done') {
                $daysPast = now()->diffInDays(Carbon::parse($task->due_date));
                $timelineStatus = "Overdue {$daysPast}d";
            }

            // Effort status
            $effortStatus = '—';
            if ($allocatedHours > 0) {
                $variance = $actualHours - $allocatedHours;
                $effortStatus = $variance > 0 ? "+{$variance}h over" : ($variance < 0 ? abs($variance) . "h under" : "On Budget");
            }

            // Extension category summary
            $categories = $taskExtensions->pluck('category')->unique()->map(fn($c) => match($c) {
                'priority_conflict' => '⏱',
                'scope_change' => '📋',
                'complexity_drag' => '⚡',
                default => $c,
            })->implode(' + ');

            $rows[] = [
                $task->title,
                $task->start_date?->format('d M Y') ?? '—',
                $task->due_date?->format('d M Y') ?? '—',
                $lastTs ? Carbon::parse($lastTs)->format('d M Y') : '—',
                $timelineStatus,
                $plannedHours . 'h',
                $allocatedHours . 'h',
                $actualHours . 'h',
                $extHours > 0 ? '+' . $extHours . 'h' : '—',
                $effortStatus,
                $extCount,
                $categories ?: '—',
            ];
        }

        // Totals row
        $rows[] = [];
        $rows[] = [
            'TOTALS', '', '', '', '',
            $this->project->tasks->sum('estimated_hours') . 'h',
            WorkAssignment::where('project_id', $this->project->id)->sum('allocated_hours') . 'h',
            Timesheet::where('project_id', $this->project->id)->whereIn('status', ['Approved','approved'])->sum('hours_spent') . 'h',
            '+' . $this->extensions->sum('hours_added') . 'h',
            '', $this->extensions->count(), '',
        ];

        return $rows;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12, 'color' => ['argb' => 'FFFFFFFF']], 'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF59E0B']]],
            4 => ['font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']], 'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF92400E']]],
        ];
    }

    public function columnWidths(): array
    {
        return ['A' => 35, 'B' => 15, 'C' => 15, 'D' => 18, 'E' => 18, 'F' => 14, 'G' => 16, 'H' => 14, 'I' => 14, 'J' => 16, 'K' => 12, 'L' => 25];
    }
}
