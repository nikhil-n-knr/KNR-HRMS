<?php

namespace App\Exports\Sheets;

use App\Models\Project;
use App\Models\ProjectExtension;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Maatwebsite\Excel\Events\AfterSheet;

class ExtensionSummarySheet implements FromArray, WithTitle, WithStyles, WithColumnWidths
{
    private $project;
    private $allocatedHours;
    private $actualHours;
    private $extensions;

    public function __construct(Project $project, float $allocatedHours, float $actualHours, $extensions)
    {
        $this->project        = $project;
        $this->allocatedHours = $allocatedHours;
        $this->actualHours    = $actualHours;
        $this->extensions     = $extensions;
    }

    public function title(): string { return 'Summary'; }

    public function array(): array
    {
        $totalDays  = $this->extensions->sum('days_added');
        $totalHours = $this->extensions->sum('hours_added');
        $origDays   = 0;
        if ($this->project->start_date && $this->project->deadline) {
            $origDays = \Carbon\Carbon::parse($this->project->start_date)
                ->diffInDays(\Carbon\Carbon::parse($this->project->deadline));
        }
        $driftPct = $origDays > 0 ? round(($totalDays / $origDays) * 100, 1) : 0;

        return [
            ['Project Extension Governance Report'],
            ['Project',       $this->project->name],
            ['Project Code',  $this->project->code ?? 'N/A'],
            ['Generated On',  now()->format('d M Y, H:i')],
            [],
            ['EFFORT ANALYSIS'],
            ['Metric',                  'Value',    'Unit'],
            ['Estimated Man-Hours',     $this->project->original_estimated_hours ?? 0,   'h'],
            ['Allocated Man-Hours',     $this->allocatedHours,   'h'],
            ['Actual Man-Hours Taken',  $this->actualHours,      'h'],
            ['Extended Efforts',        '+' . $totalHours,       'h added'],
            [],
            ['TIMELINE ANALYSIS'],
            ['Metric',                  'Value'],
            ['Original Baseline End',   $this->project->original_planned_deadline ?? $this->project->deadline ?? 'N/A'],
            ['Current Deadline',        $this->project->deadline ?? 'N/A'],
            ['Total Days Drifted',      '+' . $totalDays . ' days'],
            ['Drift Percentage',        $driftPct . '%'],
            ['Total Extensions',        $this->extensions->count()],
            [],
            ['CATEGORY BREAKDOWN'],
            ['Category',                'Extensions', 'Days Added', 'Hours Added'],
            ['⏱ Priority Conflict',    $this->extensions->where('category','priority_conflict')->count(),
                                        $this->extensions->where('category','priority_conflict')->sum('days_added'),
                                        $this->extensions->where('category','priority_conflict')->sum('hours_added')],
            ['📋 Scope Change',         $this->extensions->where('category','scope_change')->count(),
                                        $this->extensions->where('category','scope_change')->sum('days_added'),
                                        $this->extensions->where('category','scope_change')->sum('hours_added')],
            ['⚡ Complexity Drag',      $this->extensions->where('category','complexity_drag')->count(),
                                        $this->extensions->where('category','complexity_drag')->sum('days_added'),
                                        $this->extensions->where('category','complexity_drag')->sum('hours_added')],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1  => ['font' => ['bold' => true, 'size' => 14], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF4F46E5']], 'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF'], 'size' => 13]],
            6  => ['font' => ['bold' => true], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFE0E7FF']]],
            7  => ['font' => ['bold' => true, 'color' => ['argb' => 'FF4F46E5']]],
            13 => ['font' => ['bold' => true], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFEF3C7']]],
            14 => ['font' => ['bold' => true, 'color' => ['argb' => 'FFD97706']]],
            20 => ['font' => ['bold' => true], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFCE7F3']]],
            21 => ['font' => ['bold' => true, 'color' => ['argb' => 'FFBE185D']]],
        ];
    }

    public function columnWidths(): array
    {
        return ['A' => 35, 'B' => 25, 'C' => 20, 'D' => 20];
    }
}
