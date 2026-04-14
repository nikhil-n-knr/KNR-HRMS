<?php

namespace App\Http\Controllers\ProjectManagement;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectDocument;
use App\Models\ProjectStatusLog;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class ManagementDealingController extends Controller
{
    /**
     * Dashboard: Executive Dealing Hub (Management Only)
     */
    public function hub(Request $request)
    {
        // Governance check: Admin/Management Only
        if (!$request->user()->hasRole(['Super Admin', 'Admin', 'management'])) {
            abort(403, 'Unauthorized Access to Governance Vault.');
        }

        $projects = Project::with(['client'])
            ->withCount(['tasks', 'assignments'])
            ->get()
            ->map(function ($project) {
                // Get Requirement Sign-off State
                $brd = ProjectDocument::where('project_id', $project->id)
                    ->where('category', 'requirement')
                    ->where('is_current', true)
                    ->first();

                // Get Latest Manual Change History
                $lastChange = ProjectStatusLog::where('project_id', $project->id)
                    ->with('user')
                    ->latest()
                    ->first();

                return [
                    'id' => $project->id,
                    'name' => $project->name,
                    'code' => $project->code,
                    'client' => $project->client->name ?? 'External',
                    'progress' => $project->manual_progress_percentage ?? 0,
                    'health' => $project->project_health_index ?? 100,
                    'brd_signed' => $brd ? $brd->is_signed : false,
                    'brd_version' => $brd ? 'V'.$brd->version_number : 'N/A',
                    'last_audit' => $lastChange ? [
                        'by' => $lastChange->user->name,
                        'at' => $lastChange->created_at->format('d M, H:i'),
                        'reason' => $lastChange->reason
                    ] : null,
                    'tasks_count' => $project->tasks_count,
                    'members_count' => $project->assignments_count,
                ];
            });

        // Portfolio Global Stats
        $stats = [
            'total_valuation' => 'N/A', // Potentially connect to Deal values later
            'compliance_rate' => $projects->where('brd_signed', true)->count() > 0 
                ? round(($projects->where('brd_signed', true)->count() / $projects->count()) * 100) 
                : 0,
            'portfolio_health' => round($projects->avg('health')),
            'active_dealing' => $projects->count()
        ];

        return Inertia::render('Project/Management/DealingHub', [
            'projects' => $projects,
            'stats' => $stats
        ]);
    }
}
