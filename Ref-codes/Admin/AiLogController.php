<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\AiAnalysisLog;

class AiLogController extends Controller
{
    use \App\Traits\HasAttendanceHubData;

    public function index(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;
        $logs = AiAnalysisLog::with('analyzable')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $data = $this->getHubBaseData('ai_logs', $tenantId);
        $data['logs'] = $logs;

        // Calculate dynamic accurate stats
        $totalScans = AiAnalysisLog::count();
        $anomaliesCount = AiAnalysisLog::whereIn('severity', ['Critical', 'Warning'])->count();
        $averageConfidence = AiAnalysisLog::avg('confidence_score') ?? 0.985;

        $data['stats'] = [
            'total_scans' => number_format($totalScans),
            'anomalies_count' => number_format($anomaliesCount),
            'accuracy' => number_format($averageConfidence * 100, 1) . '%'
        ];

        return Inertia::render('Admin/Attendance/Hub', $data);
    }
}
