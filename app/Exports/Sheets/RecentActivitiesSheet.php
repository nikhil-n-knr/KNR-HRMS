<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RecentActivitiesSheet implements FromCollection, WithHeadings, WithTitle, WithStyles
{
    protected $projectId;
    protected $startDate;
    protected $endDate;

    public function __construct($projectId, $startDate, $endDate)
    {
        $this->projectId = $projectId;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function collection()
    {
        $query = \App\Models\TaskActivity::with(['user', 'task.project'])
            ->whereBetween('created_at', [$this->startDate, $this->endDate])
            ->orderBy('created_at', 'desc');

        if ($this->projectId) {
            $query->whereHas('task', function($q) {
                $q->where('project_id', $this->projectId);
            });
        }

        // Limit to 500 for heavy reports so it doesn't crash memory, although export jobs can handle more.
        $activities = $query->take(500)->get();

        $data = collect();

        foreach ($activities as $act) {
            $projectStr = $act->task && $act->task->project ? $act->task->project->name : '-';
            $taskStr = $act->task ? $act->task->title : '-';
            
            $details = '';
            if (is_array($act->details)) {
                $detailsParts = [];
                foreach ($act->details as $key => $val) {
                    if (is_array($val)) {
                        $detailsParts[] = ucfirst($key) . ': ' . json_encode($val);
                    } else {
                        $detailsParts[] = ucfirst($key) . ': ' . $val;
                    }
                }
                $details = implode(' | ', $detailsParts);
            } else {
                $details = $act->details;
            }

            $data->push([
                $act->created_at->format('Y-m-d H:i:s'),
                $projectStr,
                $taskStr,
                $act->user ? $act->user->name : 'System',
                ucfirst(str_replace('_', ' ', $act->type)),
                $details
            ]);
        }

        // If no activities found for Tasks, let's at least return a dummy row
        if ($data->isEmpty()) {
            $data->push(['-', 'No activities found in this period', '-', '-', '-', '-']);
        }

        return $data;
    }

    public function headings(): array
    {
        return [
            'Timestamp',
            'Project',
            'Task',
            'Actor',
            'Action Type',
            'Details / Context'
        ];
    }

    public function title(): string
    {
        return 'Recent Activities (Audit)';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
