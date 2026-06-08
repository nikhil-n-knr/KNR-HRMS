<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\WorkflowInstance;
use App\Models\WorkflowApproval;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class WorkflowAnalyticsController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Workflows/Analytics');
    }

    public function stats(Request $request)
    {
        $range = $request->input('range', '30_days');
        $startDate = match($range) {
            '7_days' => now()->subDays(7),
            '90_days' => now()->subDays(90),
            'year' => now()->subYear(),
            default => now()->subDays(30),
        };

        $entityType = $request->input('entity_type');

        $query = WorkflowInstance::where('created_at', '>=', $startDate);
        if ($entityType) {
            $query->where('entity_type', $entityType);
        }

        // 1. Total Requests by Status
        $statusCounts = (clone $query)
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');

        // 2. Avg Approval Time (Completed instances)
        $avgTime = (clone $query)->where('status', 'approved')
            ->whereNotNull('completed_at')
            ->select(DB::raw('AVG(TIMESTAMPDIFF(HOUR, started_at, completed_at)) as avg_hours'))
            ->value('avg_hours');

        // 3. Bottleneck Stages
        $bottleneckQuery = WorkflowApproval::where('workflow_approvals.status', 'pending')
            ->join('workflow_instances', 'workflow_approvals.workflow_instance_id', '=', 'workflow_instances.id')
            ->where('workflow_instances.created_at', '>=', $startDate);
        
        if ($entityType) {
            $bottleneckQuery->where('workflow_instances.entity_type', $entityType);
        }

        $bottlenecks = $bottleneckQuery
            ->with(['stage', 'workflowInstance.workflow'])
            ->select('stage_id', DB::raw('count(*) as pending_count'), DB::raw('AVG(TIMESTAMPDIFF(HOUR, workflow_approvals.created_at, NOW())) as avg_wait_hours'))
            ->groupBy('stage_id')
            ->orderByDesc('avg_wait_hours')
            ->take(5)
            ->get()
            ->map(function($approval) {
                return [
                    'stage' => $approval->stage->name ?? 'Unknown Node',
                    'workflow' => $approval->stage->workflow->name ?? 'Global Flow',
                    'pending_count' => $approval->pending_count,
                    'avg_wait_hours' => round($approval->avg_wait_hours, 1),
                ];
            });
            
        // 4. Activity Over Time (Daily)
        $activity = (clone $query)
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return response()->json([
            'status_distribution' => [
                'pending' => $statusCounts['pending'] ?? 0,
                'approved' => $statusCounts['approved'] ?? 0,
                'rejected' => $statusCounts['rejected'] ?? 0,
                'cancelled' => $statusCounts['cancelled'] ?? 0,
            ],
            'avg_approval_time' => round($avgTime ?? 0, 1),
            'bottlenecks' => $bottlenecks,
            'activity' => $activity,
            'entity_types' => WorkflowInstance::distinct()->pluck('entity_type')
        ]);
    }
}
