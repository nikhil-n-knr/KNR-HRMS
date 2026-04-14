<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EmployeeQualityTrackerSheet implements FromCollection, WithHeadings, WithTitle, WithStyles
{
    protected $bugs;

    public function __construct($bugs)
    {
        $this->bugs = $bugs;
    }

    public function collection()
    {
        $data = collect();

        foreach ($this->bugs as $bug) {
            $data->push([
                $bug->project ? $bug->project->name : 'Global',
                $bug->subject,
                $bug->severity,
                $bug->priority,
                $bug->created_at->format('Y-m-d H:i:s'),
                $bug->resolved_at ? $bug->resolved_at->format('Y-m-d H:i:s') : 'Pending',
                $bug->is_sla_breached ? 'Yes' : 'No',
                $bug->sla_due_at ? $bug->sla_due_at->format('Y-m-d H:i') : '-'
            ]);
        }

        if ($data->isEmpty()) {
            $data->push(['-', 'No bugs handled in this period', '-', '-', '-', '-', '-', '-']);
        }

        return $data;
    }

    public function headings(): array
    {
        return [
            'Project',
            'Bug Title',
            'Severity',
            'Priority',
            'Reported On',
            'Resolved On',
            'SLA Breached?',
            'SLA Deadline'
        ];
    }

    public function title(): string
    {
        return 'Quality & Bug Triaging';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
