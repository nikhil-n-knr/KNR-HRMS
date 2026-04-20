<?php

namespace App\Exports\Sheets;

use App\Models\ProjectExtension;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ExtensionLogSheet implements FromCollection, WithTitle, WithHeadings, WithMapping, WithStyles, WithColumnWidths
{
    private $projectId;

    public function __construct($projectId)
    {
        $this->projectId = $projectId;
    }

    public function title(): string { return 'Extension Log'; }

    public function collection()
    {
        return ProjectExtension::where('project_id', $this->projectId)
            ->with(['creator:id,name', 'task:id,title'])
            ->orderBy('created_at', 'asc')
            ->get();
    }

    public function headings(): array
    {
        return [
            '#',
            'Date Recorded',
            'Category',
            'Task / Scope',
            'Reason',
            'Original Start',
            'Original End',
            'Extended End',
            '+Days Added',
            '+Hours Added',
            'Conflicting Priority / Scope Type / Complexity Factor',
            'Deficit Hours / Extra Resources / Slowdown Ratio',
            'Recorded By',
        ];
    }

    public function map($ext): array
    {
        $meta = $ext->extension_meta ?? [];

        // Category label
        $catLabel = match($ext->category) {
            'priority_conflict' => '⏱ Priority Conflict',
            'scope_change'      => '📋 Scope Change',
            'complexity_drag'   => '⚡ Complexity Drag',
            default             => $ext->category ?? 'N/A',
        };

        // Meta col 1: specific context
        $metaContext = match($ext->category) {
            'priority_conflict' => $meta['conflicting_priority'] ?? '—',
            'scope_change'      => str_replace('_', ' ', $meta['scope_change_type'] ?? '—'),
            'complexity_drag'   => str_replace('_', ' ', $meta['complexity_factor'] ?? '—'),
            default             => '—',
        };

        // Meta col 2: numeric impact
        $metaImpact = match($ext->category) {
            'priority_conflict' => 'Deficit: ' . ($meta['deficit_hours'] ?? 0) . 'h',
            'scope_change'      => '+' . ($meta['additional_resources'] ?? 0) . ' resources',
            'complexity_drag'   => ($meta['slowdown_ratio'] ?? '—') . '× slowdown',
            default             => '—',
        };

        static $idx = 0;
        $idx++;

        return [
            $idx,
            $ext->created_at->format('d M Y H:i'),
            $catLabel,
            $ext->task?->title ?? 'Project Wide',
            $ext->reason,
            $ext->original_start_date?->format('d M Y') ?? '—',
            $ext->original_end_date?->format('d M Y') ?? '—',
            $ext->extended_end_date?->format('d M Y') ?? '—',
            $ext->days_added,
            $ext->hours_added,
            $metaContext,
            $metaImpact,
            $ext->creator?->name ?? 'System',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']], 'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF4F46E5']]],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,  'B' => 20, 'C' => 22, 'D' => 30,
            'E' => 40, 'F' => 16, 'G' => 16, 'H' => 16,
            'I' => 10, 'J' => 12, 'K' => 35, 'L' => 25, 'M' => 20,
        ];
    }
}
