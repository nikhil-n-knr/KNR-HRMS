<?php

namespace App\Http\Controllers\ProjectManagement;

use App\Http\Controllers\Controller;
use App\Models\BugTicket;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BugAnalyticsController extends Controller
{
    public function query(Request $request)
    {
        $request->validate([
            'groupBy' => 'required|string|in:module_id,severity,priority,workflow_stage_id,assignee_id',
            'metric' => 'required|string|in:count,avg_resolution_time',
            'project_id' => 'nullable|exists:projects,id',
            'date_start' => 'nullable|date',
            'date_end' => 'nullable|date',
        ]);

        $query = BugTicket::query();

        if ($request->project_id) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->date_start) {
            $query->where('created_at', '>=', $request->date_start);
        }

        if ($request->date_end) {
            $query->where('created_at', '<=', $request->date_end);
        }

        if ($request->metric === 'count') {
            $results = $query->select($request->groupBy, DB::raw('count(*) as value'))
                ->groupBy($request->groupBy)
                ->get();
        } else {
            // Placeholder for advanced metrics
            $results = $query->select($request->groupBy, DB::raw('count(*) as value'))
                ->groupBy($request->groupBy)
                ->get();
        }

        // Load relations for labels
        $results->load($this->getRelationForGroup($request->groupBy));

        return response()->json($results);
    }

    public function heatmap(Request $request)
    {
        $projectId = $request->project_id;

        $query = BugTicket::select('module_id', 'severity', DB::raw('count(*) as total'))
            ->whereNotNull('module_id');

        if ($projectId) {
            $query->where('project_id', $projectId);
        }

        $matrix = $query->groupBy('module_id', 'severity')->get();

        $modules = \App\Models\ProjectModule::when($projectId, function($q) use ($projectId) {
                return $q->where('project_id', $projectId);
            })
            ->whereHas('bugs')
            ->get(['id', 'name']);

        return response()->json([
            'matrix' => $matrix,
            'modules' => $modules,
            'severities' => ['critical', 'high', 'medium', 'low']
        ]);
    }

    public function godMode(Request $request)
    {
        // 1. Cost of Quality (CoQ)
        // Sum of (hours_spent * internal_cost_rate) for resolved bugs
        $coq = BugTicket::join('employees', 'bug_tickets.assignee_id', '=', 'employees.id')
            ->where('bug_tickets.assignee_type', \App\Models\Employee::class)
            ->whereNotNull('bug_tickets.hours_spent')
            ->whereNotNull('employees.internal_cost_rate')
            ->sum(DB::raw('bug_tickets.hours_spent * employees.internal_cost_rate'));

        // 2. Efficiency Metrics (Cycle & Lead Time)
        $efficiency = BugTicket::select(
            DB::raw('AVG(TIMESTAMPDIFF(HOUR, created_at, resolved_at)) as avg_lead_time_hours'),
            DB::raw('AVG(TIMESTAMPDIFF(HOUR, started_at, resolved_at)) as avg_cycle_time_hours')
        )
        ->whereNotNull('resolved_at')
        ->first();

        // 3. Technical Debt (Open Bugs by Severity * Weight)
        // Critical=10, High=5, Medium=2, Low=1
        $techDebtScore = BugTicket::select(DB::raw('
            SUM(CASE 
                WHEN severity = "critical" THEN 10 
                WHEN severity = "high" THEN 5 
                WHEN severity = "medium" THEN 2 
                ELSE 1 
            END) as debt_score
        '))
        ->whereHas('stage', function($q) {
            $q->where('is_final', false);
        })
        ->value('debt_score');

        return response()->json([
            'cost_of_quality' => round($coq, 2),
            'avg_lead_time_hours' => round($efficiency->avg_lead_time_hours ?? 0, 1),
            'avg_cycle_time_hours' => round($efficiency->avg_cycle_time_hours ?? 0, 1),
            'technical_debt_score' => $techDebtScore ?? 0
        ]);
    }

    private function getRelationForGroup($group)
    {
        return match ($group) {
            'module_id' => 'module',
            'workflow_stage_id' => 'stage',
            'assignee_id' => 'assignee',
            default => [],
        };
    }
}
