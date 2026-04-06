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
        // 1. Fetch Logs with Polymorphic Relation
        $logs = AiAnalysisLog::with('analyzable')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $data = $this->getHubBaseData('ai_logs', $tenantId);
        $data['logs'] = $logs;

        return Inertia::render('Admin/Attendance/Hub', $data);
    }
}
