<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EmployeeCrmImpactSheet implements FromCollection, WithHeadings, WithTitle, WithStyles
{
    protected $crmData;

    public function __construct($crmData)
    {
        $this->crmData = $crmData;
    }

    public function collection()
    {
        $data = collect();

        foreach ($this->crmData as $d) {
            $data->push([
                $d->title,
                $d->stage,
                $d->value,
                $d->currency ?? 'USD',
                $d->created_at ? $d->created_at->format('Y-m-d') : '-',
                $d->closed_at ? $d->closed_at->format('Y-m-d') : '-',
                $d->probability ? $d->probability . '%' : '-'
            ]);
        }

        if ($data->isEmpty()) {
            $data->push(['-', 'No CRM deals generated in this period', '-', '-', '-', '-', '-']);
        }

        return $data;
    }

    public function headings(): array
    {
        return [
            'Deal Title',
            'Pipeline Stage',
            'Deal Value',
            'Currency',
            'Created Date',
            'Closed Date',
            'Probability'
        ];
    }

    public function title(): string
    {
        return 'CRM Impact & Sales (Deals)';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
