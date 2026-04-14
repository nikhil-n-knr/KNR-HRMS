<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EmployeeTimesheetAuditSheet implements FromCollection, WithHeadings, WithTitle, WithStyles
{
    protected $sessions;

    public function __construct($sessions)
    {
        $this->sessions = $sessions;
    }

    public function collection()
    {
        $data = collect();

        foreach ($this->sessions as $s) {
            $data->push([
                $s->date,
                $s->clock_in_time,
                $s->clock_out_time ?? 'Ongoing',
                round($s->total_minutes_worked / 60, 2),
                $s->status,
                $s->is_geo_validated ? 'Yes' : 'No',
                $s->device_type ?? 'Unknown',
                $s->ip_address ?? '-'
            ]);
        }

        if ($data->isEmpty()) {
            $data->push(['-', 'No timesheets found in period', '-', '-', '-', '-', '-', '-']);
        }

        return $data;
    }

    public function headings(): array
    {
        return [
            'Date',
            'Clock-In Time',
            'Clock-Out Time',
            'Total Logged Hours',
            'Session Status',
            'Geo-Location Validated',
            'Device',
            'IP Address'
        ];
    }

    public function title(): string
    {
        return 'Attendance & Timesheet';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
