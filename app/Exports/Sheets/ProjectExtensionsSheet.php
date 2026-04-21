<?php

namespace App\Exports\Sheets;

use App\Models\ProjectExtension;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ProjectExtensionsSheet implements FromCollection, WithTitle, WithHeadings, WithMapping
{
    private $projectId;

    public function __construct($projectId)
    {
        $this->projectId = $projectId;
    }

    public function collection()
    {
        return ProjectExtension::where('project_id', $this->projectId)
            ->with('creator')
            ->orderBy('created_at', 'asc')
            ->get();
    }

    public function title(): string
    {
        return 'Timeline Extensions';
    }

    public function headings(): array
    {
        return [
            'Date Created',
            'Type',
            'Days Added',
            'Hours Added',
            'Reason',
            'Notes',
            'Recorded By'
        ];
    }

    public function map($extension): array
    {
        return [
            $extension->created_at->format('Y-m-d H:i'),
            ucfirst($extension->type),
            $extension->days_added,
            $extension->hours_added,
            $extension->reason,
            $extension->notes ?? 'N/A',
            $extension->creator?->name ?? 'System'
        ];
    }
}
