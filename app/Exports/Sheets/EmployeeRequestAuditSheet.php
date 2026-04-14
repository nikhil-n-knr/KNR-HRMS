<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\WfhRequest;
use App\Models\ShiftSwap;

class EmployeeRequestAuditSheet implements FromCollection, WithHeadings, WithTitle, WithStyles
{
    protected $employee;
    protected $start;
    protected $end;

    public function __construct(Employee $employee, $start, $end)
    {
        $this->employee = $employee;
        $this->start = $start;
        $this->end = $end;
    }

    public function collection()
    {
        $data = collect();

        // 1. Leaves
        $leaves = LeaveRequest::where('employee_id', $this->employee->id)
            ->whereBetween('created_at', [$this->start, $this->end])
            ->get();
        foreach($leaves as $l) {
            $data->push([
                'Type' => 'Leave',
                'Date/Range' => $l->start_date->format('Y-m-d') . ' to ' . $l->end_date->format('Y-m-d'),
                'Details' => $l->total_days . ' days: ' . $l->reason,
                'Status' => $l->status,
                'Ref' => $l->uuid ?? $l->id
            ]);
        }

        // 2. WFH
        $wfhs = WfhRequest::where('employee_id', $this->employee->id)
            ->whereBetween('created_at', [$this->start, $this->end])
            ->get();
        foreach($wfhs as $w) {
            $data->push([
                'Type' => 'WFH',
                'Date/Range' => $w->date->format('Y-m-d'),
                'Details' => $w->reason,
                'Status' => $w->status,
                'Ref' => $w->id
            ]);
        }

        // 3. Swaps
        $swaps = ShiftSwap::where('requestor_id', $this->employee->id)
            ->whereBetween('created_at', [$this->start, $this->end])
            ->get();
        foreach($swaps as $s) {
            $data->push([
                'Type' => 'Shift Swap',
                'Date/Range' => $s->date->format('Y-m-d'),
                'Details' => 'Swap Request',
                'Status' => $s->status,
                'Ref' => $s->id
            ]);
        }

        return $data;
    }

    public function headings(): array
    {
        return [
            'Request Type',
            'Period',
            'Reason/Details',
            'Approval Status',
            'Reference ID'
        ];
    }

    public function title(): string
    {
        return 'Request & History Audit';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
