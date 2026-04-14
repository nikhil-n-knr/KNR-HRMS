<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EmployeeOverviewSheet implements FromCollection, WithHeadings, WithTitle, WithStyles
{
    protected $employee;
    protected $metrics;
    protected $startDate;
    protected $endDate;

    public function __construct($employee, $metrics, $startDate, $endDate)
    {
        $this->employee = $employee;
        $this->metrics = $metrics;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function collection()
    {
        return collect([
            ['Period', $this->startDate->format('M d, Y') . ' - ' . $this->endDate->format('M d, Y')],
            ['Employee Name', $this->employee->user->name ?? ($this->employee->first_name . ' ' . $this->employee->last_name)],
            ['Designation', $this->employee->designation],
            ['Department', $this->employee->department->name ?? 'N/A'],
            ['Status', $this->employee->status],
            ['', ''], // empty row
            ['OPERATIONAL HEALTH (KPI)', ''],
            ['Scrum Points Velocity', $this->metrics['overview']['scrum_velocity'] ?? 0],
            ['Total Hours Burned', $this->metrics['overview']['hours_burned'] ?? 0],
            ['Attendance Reliability', ($this->metrics['overview']['reliability_score'] ?? 0) . '%'],
            ['Calculated Burnout Risk', $this->metrics['overview']['burnout_risk'] ?? 'Unknown'],
            ['', ''], // empty row
            ['PROJECT COMPLETION', ''],
            ['Tasks Completed', $this->metrics['projects']['completed'] ?? 0],
            ['Active Overdue Tasks', $this->metrics['projects']['overdue'] ?? 0],
            ['', ''], // empty row
            ['QUALITY INDEX', ''],
            ['Bugs Resolved', $this->metrics['quality']['resolved'] ?? 0],
            ['Bugs SLA Breached', $this->metrics['quality']['breached'] ?? 0]
        ]);
    }

    public function headings(): array
    {
        return [
            'Metric',
            'Value'
        ];
    }

    public function title(): string
    {
        return 'Overview & AI Health';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
            7 => ['font' => ['bold' => true, 'italic' => true]],
            13 => ['font' => ['bold' => true, 'italic' => true]],
            17 => ['font' => ['bold' => true, 'italic' => true]],
        ];
    }
}
