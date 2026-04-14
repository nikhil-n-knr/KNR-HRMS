<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TeamPerformanceSheet implements FromCollection, WithHeadings, WithTitle, WithStyles
{
    protected $assignments;
    protected $startDate;
    protected $endDate;

    public function __construct($assignments, $startDate, $endDate)
    {
        $this->assignments = $assignments;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function collection()
    {
        $performance = [];

        foreach ($this->assignments as $a) {
            $empName = $a->assignee ? $a->assignee->name : 'Unassigned';

            if (!isset($performance[$empName])) {
                $performance[$empName] = [
                    'hours_burned' => 0,
                    'scrum_points' => 0,
                    'tasks_involved' => [],
                    'overdue_tasks' => 0
                ];
            }

            // Estimate burned hours loosely from allocations as we did in main Controller
            $performance[$empName]['hours_burned'] += $a->allocated_hours; 

            if ($a->task) {
                if (!in_array($a->task->id, $performance[$empName]['tasks_involved'])) {
                    $performance[$empName]['tasks_involved'][] = $a->task->id;
                    $performance[$empName]['scrum_points'] += ($a->task->scrum_points ?? 0);
                    
                    // Check if Overdue
                    if ($a->task->status !== 'Done' && $a->task->due_date && $a->task->due_date < now()) {
                        $performance[$empName]['overdue_tasks']++;
                    }
                }
            }
        }

        $data = collect();

        foreach ($performance as $name => $metrics) {
            $data->push([
                $name,
                count($metrics['tasks_involved']),
                $metrics['scrum_points'],
                $metrics['hours_burned'],
                $metrics['overdue_tasks']
            ]);
        }

        // Sort by points desc
        return $data->sortByDesc(2);
    }

    public function headings(): array
    {
        return [
            'Resource Name',
            'Unique Tasks Handled',
            'Scrum Points Acquired',
            'Allocated Hours Burned',
            'Active Overdue Tasks'
        ];
    }

    public function title(): string
    {
        return 'Team Velocity Leaderboard';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
