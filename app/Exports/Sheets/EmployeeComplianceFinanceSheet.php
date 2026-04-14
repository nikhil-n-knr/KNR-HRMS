<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EmployeeComplianceFinanceSheet implements FromCollection, WithHeadings, WithTitle, WithStyles
{
    protected $employee;

    public function __construct($employee)
    {
        $this->employee = $employee;
    }

    public function collection()
    {
        $data = collect();

        // Assets
        // Assuming Assets exist in a potential relation standard to HRMS
        $assets = '-';
        if (method_exists($this->employee, 'assets') && $this->employee->assets) {
            $assets = $this->employee->assets->count() . ' Active Assets Assigned';
        }

        // Tax Declaration (If linked directly to Employee or User)
        $taxStatus = '-';
        if (class_exists(\App\Models\TaxDeclaration::class)) {
            $declaration = \App\Models\TaxDeclaration::where('employee_id', $this->employee->id)->latest()->first();
            $taxStatus = $declaration ? $declaration->status : 'Not Submitted';
        }

        // Document compliance (from documents table if exists)
        $docs = '-';
        if (method_exists($this->employee, 'documents') && $this->employee->documents) {
            $count = $this->employee->documents()->count();
            $docs = $count > 0 ? "$count Documents Uploaded" : 'No Documents Found';
        }

        $data->push([
            'IT Assets', $assets
        ]);
        
        $data->push([
            'Tax Declaration Status', $taxStatus
        ]);
        
        $data->push([
            'HR Documents (KYC) Status', $docs
        ]);

        return $data;
    }

    public function headings(): array
    {
        return [
            'Compliance Module',
            'Current Status'
        ];
    }

    public function title(): string
    {
        return 'Financial & Compliance Health';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
