<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TaskResourceUtilizationSheet implements FromCollection, WithHeadings, WithTitle, WithStyles
{
    protected $assignments;
    protected $holidays;
    protected $startDate;
    protected $endDate;

    public function __construct($assignments, $holidays, $startDate, $endDate)
    {
        $this->assignments = $assignments;
        $this->holidays = $holidays;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function collection()
    {
        $data = collect();

        foreach ($this->assignments as $a) {
            $s = $a->start_date < $this->startDate ? $this->startDate->copy() : $a->start_date->copy();
            $e = $a->end_date > $this->endDate ? $this->endDate->copy() : $a->end_date->copy();
            
            $regularHours = 0;
            $holidayHours = 0;
            
            $curr = $s->copy();
            while ($curr <= $e) {
                // Carbon isWeekend() is Saturday/Sunday. 
                $isWeekend = $curr->isWeekend(); 
                $dateStr = $curr->format('Y-m-d');
                $isHoliday = isset($this->holidays[$dateStr]);
                
                if ($isWeekend || $isHoliday) {
                    if ($a->force_allocation) $holidayHours += $a->allocated_hours;
                } else {
                    $regularHours += $a->allocated_hours;
                }
                $curr->addDay();
            }

            $total = $regularHours + $holidayHours;
            if ($total == 0) continue; 

            // Check governance baseline deviation if task is loaded
            $deviation = 'None';
            if ($a->task && $a->task->baseline_due_date && $a->task->due_date) {
                if ($a->task->due_date > $a->task->baseline_due_date) {
                    $deviation = 'Overrun (+'. $a->task->baseline_due_date->diffInDays($a->task->due_date) . ' days)';
                }
            }

            $data->push([
                $a->project ? $a->project->name : 'Unknown',
                $a->task ? $a->task->title : 'N/A',
                $a->task ? $a->task->status : 'N/A',
                $a->assignee ? $a->assignee->name : 'Unassigned',
                $s->format('Y-m-d'),
                $e->format('Y-m-d'),
                $a->allocated_hours,
                $a->task ? $a->task->estimated_hours : 0,
                $a->task ? $a->task->scrum_points : 0,
                $total,
                $holidayHours,
                $deviation,
                $a->force_allocation ? 'Forced (Includes Weekends/Holidays)' : 'Standard'
            ]);
        }

        return $data;
    }

    public function headings(): array
    {
        return [
            'Project',
            'Task',
            'Status',
            'Assigned Employee',
            'Start Date (In Period)',
            'End Date (In Period)',
            'Daily Allocated Hours',
            'Total Estimated Hours (Task)',
            'Scrum Points',
            'Total Hours Burned (Period)',
            'Holiday/Weekend Hours',
            'Schedule Deviation',
            'Allocation Type'
        ];
    }

    public function title(): string
    {
        return 'Task & Resource Utilization';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
