<?php

namespace App\Http\Controllers\ProjectManagement;

use App\Http\Controllers\Controller;
use App\Models\BugTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
// Note: Assuming Barryvdh\DomPDF\Facade\Pdf is available, otherwise will use standard blade stream
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    public function generate(Request $request)
    {
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'format' => 'required|in:pdf,csv',
            'columns' => 'required|array',
            'filters' => 'nullable|array'
        ]);

        $query = BugTicket::where('project_id', $request->project_id)
            ->with(['module', 'stage', 'reporter', 'assignee']);

        // Apply dynamic filters
        if (!empty($request->filters['severity'])) {
            $query->whereIn('severity', $request->filters['severity']);
        }
        if (!empty($request->filters['priority'])) {
            $query->whereIn('priority', $request->filters['priority']);
        }
        if (!empty($request->filters['stage_ids'])) {
            $query->whereIn('workflow_stage_id', $request->filters['stage_ids']);
        }

        $bugs = $query->get();

        if ($request->format === 'csv') {
            return $this->exportCSV($bugs, $request->columns);
        }

        return $this->exportPDF($bugs, $request->columns, $request->project_id);
    }

    protected function exportCSV($bugs, $columns)
    {
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=engineering_audit_" . date('Y-m-d') . ".csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use($bugs, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($bugs as $bug) {
                $row = [];
                foreach ($columns as $col) {
                    $row[] = $this->getColumnValue($bug, $col);
                }
                fputcsv($file, $row);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    protected function exportPDF($bugs, $columns, $projectId)
    {
        $project = \App\Models\Project::find($projectId);
        
        // Use a simple layout for the PDF
        $pdf = Pdf::loadView('exports.bug_report', [
            'bugs' => $bugs,
            'columns' => $columns,
            'project' => $project,
            'generated_at' => now()
        ]);

        return $pdf->download('operational_audit_' . $project->name . '_' . date('Y-m-d') . '.pdf');
    }

    protected function getColumnValue($bug, $column)
    {
        switch (strtolower($column)) {
            case 'id': return $bug->id;
            case 'subject': return $bug->subject;
            case 'module': return $bug->module?->name ?? 'N/A';
            case 'stage': return $bug->stage?->name;
            case 'severity': return $bug->severity;
            case 'priority': return $bug->priority;
            case 'reporter': return $bug->reporter?->name;
            case 'assignee': return $bug->assignee?->name ?? 'Unassigned';
            case 'created': return $bug->created_at->format('Y-m-d');
            default: return '';
        }
    }
}
