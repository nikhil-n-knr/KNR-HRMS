<?php

namespace App\Http\Controllers\ProjectManagement;

use App\Http\Controllers\Controller;
use App\Models\GitCommit;
use App\Models\GitPullRequest;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

use Inertia\Inertia;

class DevOpsDashboardController extends Controller
{
    public function index(Request $request, Project $project)
    {
        return Inertia::render('Project/DevOps/Dashboard', [
            'project' => $project,
            'repos' => $project->repositories
        ]);
    }

    public function export(Request $request, $projectId)
    {
        $type = $request->get('type', 'pdf');
        $project = Project::findOrFail($projectId);
        $repoIds = $project->repositories()->pluck('id');
        
        $stats = [
            'Total Commits' => GitCommit::whereIn('git_repository_id', $repoIds)->count(),
            'Open PRs' => GitPullRequest::whereIn('git_repository_id', $repoIds)->where('state', 'open')->count(),
            'Merged PRs' => GitPullRequest::whereIn('git_repository_id', $repoIds)->where('state', 'merged')->count(),
        ];
        
        $commits = GitCommit::whereIn('git_repository_id', $repoIds)
            ->with(['repository'])
            ->orderByDesc('committed_at')
            ->take(100)
            ->get();

        if ($type === 'excel') {
            $headers = [
                "Content-type"        => "text/csv",
                "Content-Disposition" => "attachment; filename=project_devops_overview.csv",
                "Pragma"              => "no-cache",
                "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
                "Expires"             => "0"
            ];
            $callback = function() use($stats, $commits) {
                $file = fopen('php://output', 'w');
                fputcsv($file, ['Metric', 'Value']);
                foreach($stats as $k => $v) { fputcsv($file, [$k, $v]); }
                fputcsv($file, []);
                fputcsv($file, ['Repository', 'Author', 'Message', 'Date']);
                foreach($commits as $c) {
                    $repoName = $c->repository ? $c->repository->name : 'Unknown';
                    fputcsv($file, [$repoName, $c->author_name, $c->message, $c->committed_at]);
                }
                fclose($file);
            };
            return response()->stream($callback, 200, $headers);
        }
        
        // PDF Export
        $html = "<h1>DevOps Overview: {$project->name}</h1><hr/>";
        foreach($stats as $k => $v) { $html .= "<p><b>{$k}:</b> {$v}</p>"; }
        $html .= '<h2>Recent Commits</h2><table border="1" cellpadding="5" cellspacing="0" style="width:100%;text-align:left;border-collapse:collapse;"><tr><th>Repo</th><th>Author</th><th>Message</th><th>Date</th></tr>';
        foreach($commits as $c) { 
            $repoName = $c->repository ? $c->repository->name : 'Unknown';
            $html .= "<tr><td>{$repoName}</td><td>{$c->author_name}</td><td>{$c->message}</td><td>{$c->committed_at}</td></tr>"; 
        }
        $html .= '</table>';
        
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html);
        return $pdf->download("project_{$project->id}_devops.pdf");
    }
    /**
     * Tab A: Pulse (Velocity & Bottlenecks)
     */
    // --- Tab A: Pulse (High-Level Health) ---
    public function getPulseStats(Request $request, $projectId)
    {
        $repoIds = Project::findOrFail($projectId)->repositories()->pluck('id');
        $now = Carbon::now();
        $startOfWeek = $now->copy()->startOfWeek();
        $lastWeekStart = $startOfWeek->copy()->subWeek();
        
        // 1. Velocity (Commits this week vs last)
        $commitsThisWeek = GitCommit::whereIn('git_repository_id', $repoIds)
            ->where('committed_at', '>=', $startOfWeek)
            ->count();
            
        $commitsLastWeek = GitCommit::whereIn('git_repository_id', $repoIds)
            ->whereBetween('committed_at', [$lastWeekStart, $startOfWeek])
            ->count();
            
        $velocityChange = $commitsLastWeek > 0 
            ? round((($commitsThisWeek - $commitsLastWeek) / $commitsLastWeek) * 100) 
            : 0;

        // 2. Sparkline (Last 30 Days)
        $sparkline = GitCommit::whereIn('git_repository_id', $repoIds)
            ->where('committed_at', '>=', $now->copy()->subDays(30))
            ->selectRaw('DATE(committed_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // 3. PR Throughput (Opened vs Merged in last 30 days)
        $prsOpened = GitPullRequest::whereIn('git_repository_id', $repoIds)
            ->where('created_at_provider', '>=', $now->copy()->subDays(30))
            ->count();
            
        $prsMerged = GitPullRequest::whereIn('git_repository_id', $repoIds)
            ->where('merged_at', '>=', $now->copy()->subDays(30))
            ->count();

        // 4. Code Churn (New vs Refactored - Approximation using Additions/Deletions)
        $churnStats = GitCommit::whereIn('git_repository_id', $repoIds)
             ->where('committed_at', '>=', $now->copy()->subDays(30))
             ->selectRaw('SUM(additions) as added, SUM(deletions) as deleted')
             ->first();
             
        $totalLines = ($churnStats->added + $churnStats->deleted) ?: 1;
        $churnRate = round(($churnStats->deleted / $totalLines) * 100);

        // 5. Punch Card (Heatmap)
        $heatmap = GitCommit::whereIn('git_repository_id', $repoIds)
            ->where('committed_at', '>=', $now->copy()->subMonths(3)) 
            ->selectRaw('DAYOFWEEK(committed_at) as day, HOUR(committed_at) as hour, COUNT(*) as count') 
            ->groupBy('day', 'hour')
            ->get();

        // 6. DORA Metrics Approximation
        $deployFreq = round($prsMerged / 4, 1); // Weekly deploy freq approx
        $leadTimeHours = round(GitPullRequest::whereIn('git_repository_id', $repoIds)
            ->whereNotNull('merged_at')
            ->where('created_at_provider', '>=', $now->copy()->subDays(30))
            ->get()
            ->map(fn($pr) => $pr->created_at_provider->diffInHours($pr->merged_at))
            ->avg() ?? 0);
            
        $failedChanges = GitPullRequest::whereIn('git_repository_id', $repoIds)
            ->where('merged_at', '>=', $now->copy()->subDays(30))
            ->where(function($q) {
                $q->where('title', 'like', '%fix%')
                  ->orWhere('title', 'like', '%bug%')
                  ->orWhere('title', 'like', '%revert%');
            })->count();
            
        $cfr = $prsMerged > 0 ? round(($failedChanges / $prsMerged) * 100) : 0;

        return response()->json([
            'velocity' => [
                'current' => $commitsThisWeek,
                'change' => $velocityChange,
                'sparkline' => $sparkline
            ],
            'throughput' => [
                'opened' => $prsOpened,
                'merged' => $prsMerged
            ],
            'churn' => [
                'rate' => $churnRate,
                'new_code' => 100 - $churnRate
            ],
            'heatmap' => $heatmap,
            'dora' => [
                'deployment_frequency' => $deployFreq,
                'lead_time' => $leadTimeHours,
                'change_failure_rate' => $cfr
            ]
        ]);
    }

    // --- Tab B: PR Analytics (Bottlenecks) ---
    public function getPrAnalytics(Request $request, $projectId)
    {
        $repoIds = Project::findOrFail($projectId)->repositories()->pluck('id');
        
        // 1. Lifespan Histogram (Hours to Merge) - Only merged PRs
        $mergedPrs = GitPullRequest::whereIn('git_repository_id', $repoIds)
            ->whereNotNull('merged_at')
            ->whereNotNull('created_at_provider') 
            ->get() 
            ->map(function($pr) {
                return $pr->created_at_provider->diffInHours($pr->merged_at);
            });
            
        $histogram = [
            '0-4h' => $mergedPrs->filter(fn($h) => $h < 4)->count(),
            '4-24h' => $mergedPrs->filter(fn($h) => $h >= 4 && $h < 24)->count(),
            '1-2d' => $mergedPrs->filter(fn($h) => $h >= 24 && $h < 48)->count(),
            '3d+' => $mergedPrs->filter(fn($h) => $h >= 48)->count(),
        ];

        // 2. Reviewer Load Balancer (Pending vs Merged count per Reviewer)
        // Ensure GitPrReview model is imported or use full path
        $reviews = \App\Models\GitPrReview::whereHas('pullRequest', fn($q) => $q->whereIn('git_repository_id', $repoIds))
            ->select('reviewer_name', 'state')
            ->get()
            ->groupBy('reviewer_name');

        $loadBalancer = $reviews->map(function($items, $name) {
            return [
                'name' => $name,
                'pending' => $items->whereIn('state', ['PENDING', 'COMMENTED', 'CHANGES_REQUESTED'])->count(),
                'merged' => $items->where('state', 'APPROVED')->count()
            ];
        })->values()->sortByDesc('pending')->take(10)->values(); 

        // 3. The "Stuck" List (Open PRs sorted by Age)
        $stuckPrs = GitPullRequest::whereIn('git_repository_id', $repoIds)
            ->where('state', 'open')
            ->with(['repository'])
            ->orderBy('created_at_provider', 'asc') // Oldest first
            ->take(20)
            ->get()
            ->map(function($pr) {
                return [
                    'id' => $pr->id,
                    'title' => $pr->title,
                    'author' => $pr->author_name,
                    'repo' => $pr->repository->name,
                    'age_days' => $pr->created_at_provider ? $pr->created_at_provider->diffInDays(now()) : 0,
                    'url' => '#', 
                    'is_stale' => $pr->created_at_provider && $pr->created_at_provider->diffInDays(now()) > 3
                ];
            });

        return response()->json([
            'lifespan' => $histogram,
            'load_balancer' => $loadBalancer,
            'stuck_list' => $stuckPrs
        ]);
    }

    // --- Tab C: Review Intelligence (Quality) ---
    public function getReviewIntelligence(Request $request, $projectId)
    {
        $repoIds = Project::findOrFail($projectId)->repositories()->pluck('id');
        
        // 1. Rubber Stamp Detector (Scatter Plot: Lines vs Time to Approve)
        $rubberStamps = \App\Models\GitPrReview::where('state', 'APPROVED')
            ->whereHas('pullRequest', fn($q) => $q->whereIn('git_repository_id', $repoIds))
            ->with('pullRequest') 
            ->whereNotNull('submitted_at')
            ->get()
            ->map(function($review) {
                $pr = $review->pullRequest;
                if (!$pr || !$pr->created_at_provider) return null;
                
                $minsToApprove = $pr->created_at_provider->diffInMinutes($review->submitted_at);
                $size = $pr->additions + $pr->deletions;
                
                return [
                    'x' => $size, // Lines
                    'y' => $minsToApprove, // Minutes
                    'reviewer' => $review->reviewer_name,
                    'pr_title' => $pr->title,
                    'flagged' => ($size > 500 && $minsToApprove < 10) 
                ];
            })
            ->filter()
            ->values();

        // 2. Depth Score (Comments per Review)
        $depthScore = \App\Models\GitPrReview::whereHas('pullRequest', fn($q) => $q->whereIn('git_repository_id', $repoIds))
             ->selectRaw('reviewer_name, AVG(comments_count) as avg_comments, COUNT(*) as total_reviews')
             ->groupBy('reviewer_name')
             ->having('total_reviews', '>', 3) 
             ->orderByDesc('avg_comments')
             ->limit(10)
             ->get();

        return response()->json([
            'rubber_stamps' => $rubberStamps,
            'depth_leaderboard' => $depthScore
        ]);
    }

    /**
     * Tab C: Commit Stream
     */
    public function getCommits(Request $request, $projectId)
    {
        $repoIds = Project::findOrFail($projectId)->repositories()->pluck('id');
        
        $commits = GitCommit::with(['repository', 'tasks:id,title,status'])
            ->whereIn('git_repository_id', $repoIds)
            ->orderBy('committed_at', 'desc')
            ->paginate(20);

        return response()->json($commits);
    }
}
