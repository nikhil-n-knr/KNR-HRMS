<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EmployeeAttendanceLogSheet implements FromCollection, WithHeadings, WithTitle, WithStyles
{
    protected $attendanceGrid;

    public function __construct($attendanceGrid)
    {
        $this->attendanceGrid = $attendanceGrid;
    }

    public function collection()
    {
        return collect($this->attendanceGrid);
    }

    public function headings(): array
    {
        return [
            'Date',
            'Status',
            'Check In',
            'Check Out',
            'Duration (Hrs)'
        ];
    }

    public function title(): string
    {
        return 'Daily Attendance Log';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
