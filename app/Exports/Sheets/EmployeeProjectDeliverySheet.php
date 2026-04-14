<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EmployeeProjectDeliverySheet implements FromCollection, WithHeadings, WithTitle, WithStyles
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
        $data = collect();

        foreach ($this->assignments as $a) {
            $deviation = 'None';
            if ($a->task && $a->task->baseline_due_date && $a->task->due_date) {
                if ($a->task->due_date > $a->task->baseline_due_date) {
                    $deviation = 'Overrun (+'. $a->task->baseline_due_date->diffInDays($a->task->due_date) . 'd)';
                }
            }

            $data->push([
                $a->project ? $a->project->name : 'Unknown',
                $a->task ? $a->task->title : 'N/A',
                $a->task ? $a->task->status : 'N/A',
                $a->start_date->format('Y-m-d'),
                $a->end_date->format('Y-m-d'),
                $a->allocated_hours,
                $a->task ? $a->task->scrum_points : 0,
                $deviation,
                $a->force_allocation ? 'Yes' : 'No'
            ]);
        }

        if ($data->isEmpty()) {
            $data->push(['-', 'No project tasks assigned', '-', '-', '-', '-', '-', '-', '-']);
        }

        return $data;
    }

    public function headings(): array
    {
        return [
            'Project',
            'Task Title',
            'Status',
            'Assignment Start',
            'Assignment End',
            'Allocated Hrs/Day',
            'Scrum Points',
            'Schedule Deviation',
            'Forced Weekend/Holiday?'
        ];
    }

    public function title(): string
    {
        return 'Project Delivery Strategy';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
