<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EmployeeLearningSheet implements FromCollection, WithHeadings, WithTitle, WithStyles
{
    protected $learningData;

    public function __construct($learningData)
    {
        $this->learningData = $learningData;
    }

    public function collection()
    {
        $data = collect();

        foreach ($this->learningData as $d) {
            $data->push([
                $d->course ? $d->course->title : 'Deleted Course',
                $d->progress_percentage . '%',
                $d->status,
                $d->started_at ? $d->started_at->format('Y-m-d') : '-',
                $d->completed_at ? $d->completed_at->format('Y-m-d') : '-',
                $d->course ? $d->course->difficulty_level : '-',
            ]);
        }

        if ($data->isEmpty()) {
            $data->push(['-', 'No LMS data found', '-', '-', '-', '-']);
        }

        return $data;
    }

    public function headings(): array
    {
        return [
            'Course Title',
            'Progress %',
            'Current Status',
            'Enrollment Date',
            'Completion Date',
            'Course Difficulty'
        ];
    }

    public function title(): string
    {
        return 'Learning & Progress (LMS)';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
