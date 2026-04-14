<?php

namespace App\Http\Controllers\ProjectManagement;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectDocument;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

class ProjectSummaryController extends Controller
{
    /**
     * Export High-Fidelity Project Summary PDF
     */
    public function exportPDF(Project $project)
    {
        $this->authorize('view', $project);

        $project->load(['client', 'documents' => function($q) {
            $q->where('is_signed', true)->orderBy('signed_at', 'desc');
        }]);

        // Aggregate project metrics
        $data = [
            'project' => $project,
            'client' => $project->client,
            'progress' => $project->manual_progress_percentage ?? 0,
            'health' => $project->project_health_index ?? 100,
            'signed_documents' => $project->documents,
            'exported_at' => now()->format('d M Y, H:i'),
            'exported_by' => Auth::user()->name
        ];

        $pdf = Pdf::loadView('reports.project-summary-pdf', $data);

        return $pdf->download("Project_Summary_{$project->code}.pdf");
    }
}
