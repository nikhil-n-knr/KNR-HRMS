<?php

namespace App\Exports\ProjectManagement;

use App\Models\Project;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProjectWeeklyExport implements FromCollection, WithHeadings, WithMapping, WithTitle, ShouldAutoSize, WithStyles
{
    protected $project;
    protected $startDate;
    protected $endDate;
    protected $data;

    public function __construct(Project $project, Carbon $startDate, Carbon $endDate, array $data)
    {
        $this->project = $project;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->data = $data;
    }

    public function collection()
    {
        return collect($this->data['report']);
    }

    public function title(): string
    {
        return 'Weekly Analytics';
    }

    public function headings(): array
    {
        return [
            'User ID', 'Name', 'Planned Hours (Matrix)', 'Actual Hours (Timesheets)', 'Deviation', 'Status'
        ];
    }

    public function map($row): array
    {
        return [
            $row['user_id'],
            $row['name'],
            $row['planned'],
            $row['actual'],
            $row['deviation'],
            $row['status']
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Add Summary Row at the bottom
        $lastRow = count($this->data['report']) + 2;
        $sheet->setCellValue("A{$lastRow}", "TOTALS");
        $sheet->setCellValue("C{$lastRow}", $this->data['totals']['planned']);
        $sheet->setCellValue("D{$lastRow}", $this->data['totals']['actual']);
        $sheet->setCellValue("E{$lastRow}", $this->data['totals']['deviation']);

        return [
            1 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '4F46E5']]],
            $lastRow => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'F1F5F9']]],
        ];
    }
}
