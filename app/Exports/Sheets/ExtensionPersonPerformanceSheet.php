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

class ExtensionPersonPerformanceSheet implements FromArray, WithTitle, WithStyles, WithColumnWidths
{
    private $project;
    private $extensions;

    public function __construct(Project $project, $extensions)
    {
        $this->project    = $project;
        $this->extensions = $extensions;
    }

    public function title(): string { return 'Person Performance'; }

    public function array(): array
    {
        $rows = [
            ['Person Performance Report — Project: ' . $this->project->name],
            ['Generated: ' . now()->format('d M Y H:i')],
            [],
            ['Name', 'Total Tasks', 'On Time', 'Delayed', 'Completed Early', 'Total Delay Days', 'Days Saved', 'On-Time %', 'Extensions Caused', 'Status'],
        ];

        $tasks = $this->project->tasks()
            ->with(['assignees:id,name', 'timesheets' => fn($q) => $q->whereIn('status', ['Approved', 'approved'])])
            ->whereNotNull('due_date')
            ->get();

        $personPerf = [];

        foreach ($tasks as $task) {
            $lastTs = $task->timesheets->max('date');
            $dueDate = Carbon::parse($task->due_date);
            $taskExtensions = $this->extensions->where('task_id', $task->id);

            foreach ($task->assignees as $user) {
                $uid = $user->id;
                if (!isset($personPerf[$uid])) {
                    $personPerf[$uid] = ['name' => $user->name, 'tasks' => 0, 'on_time' => 0, 'delayed' => 0, 'faster' => 0, 'delay_days' => 0, 'saved_days' => 0, 'ext_count' => 0];
                }
                $personPerf[$uid]['tasks']++;
                $personPerf[$uid]['ext_count'] += $taskExtensions->count();

                if ($lastTs) {
                    $diff = $dueDate->diffInDays(Carbon::parse($lastTs), false);
                    if ($diff > 0) { $personPerf[$uid]['delayed']++; $personPerf[$uid]['delay_days'] += $diff; }
                    elseif ($diff < 0) { $personPerf[$uid]['faster']++; $personPerf[$uid]['saved_days'] += abs($diff); }
                    else { $personPerf[$uid]['on_time']++; }
                }
            }
        }

        foreach ($personPerf as $p) {
            $onTimePct = $p['tasks'] > 0 ? round(($p['on_time'] / $p['tasks']) * 100) : 0;
            $status = $p['delayed'] > 0 && $p['delayed'] >= $p['faster']
                ? '⚠️ Risk'
                : ($p['faster'] > $p['delayed'] ? '✅ High Performer' : '🟡 Moderate');

            $rows[] = [
                $p['name'],
                $p['tasks'],
                $p['on_time'],
                $p['delayed'],
                $p['faster'],
                '+' . $p['delay_days'] . 'd',
                '-' . $p['saved_days'] . 'd',
                $onTimePct . '%',
                $p['ext_count'],
                $status,
            ];
        }

        return $rows;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12, 'color' => ['argb' => 'FFFFFFFF']], 'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF10B981']]],
            4 => ['font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']], 'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF065F46']]],
        ];
    }

    public function columnWidths(): array
    {
        return ['A' => 25, 'B' => 12, 'C' => 12, 'D' => 12, 'E' => 16, 'F' => 18, 'G' => 14, 'H' => 12, 'I' => 20, 'J' => 22];
    }
}
