<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProjectOverviewSheet implements FromCollection, WithHeadings, WithTitle, WithStyles
{
    protected $stats;
    protected $startDate;
    protected $endDate;
    protected $projectId;

    public function __construct($stats, $startDate, $endDate, $projectId = null)
    {
        $this->stats = $stats;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->projectId = $projectId;
    }

    public function collection()
    {
        $scopeStr = $this->projectId ? "Specific Project Details" : "All Global Projects";

        return collect([
            [
                'Period',
                $this->startDate->format('M d, Y') . ' - ' . $this->endDate->format('M d, Y'),
            ],
            [
                'Project Scope Filter',
                $scopeStr,
            ],
            [
                'Total Resource Count (Active)',
                $this->stats['resource_count'] ?? 0,
            ],
            [
                'Total Hours Burned',
                ($this->stats['total_hours'] ?? 0) . ' hours',
            ],
            [
                'Of which Holiday/Weekend Hours',
                ($this->stats['holiday_hours'] ?? 0) . ' hours',
            ],
            [
                'Average Daily Burn',
                ($this->stats['avg_daily'] ?? 0) . ' hours/day',
            ],
            [
                'Total Project Scope (Estimated Hours)',
                ($this->stats['total_scope'] ?? 0) . ' hours',
            ],
            [
                'Remaining Hours (From Scope)',
                ($this->stats['remaining_hours'] ?? 0) . ' hours',
            ],
            [
                'Total Scrum Points',
                $this->stats['total_points'] ?? 0,
            ]
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
        return 'Overview Dashboard';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }
}
