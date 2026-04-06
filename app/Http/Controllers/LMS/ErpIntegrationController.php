<?php

namespace App\Http\Controllers\LMS;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\LMS\LmsErpSyncLog;
use App\Models\LMS\LmsInstitution;
use App\Services\LMS\ErpIntegrationService;

class ErpIntegrationController extends Controller
{
    protected $erpService;

    public function __construct(ErpIntegrationService $erpService)
    {
        $this->erpService = $erpService;
    }

    /**
     * Display the ERP sync management hub.
     */
    public function index()
    {
        return Inertia::render('LMS/Admin/Erp/Index', [
            'sync_logs'    => LmsErpSyncLog::with('institution')->latest()->limit(20)->get(),
            'institutions' => LmsInstitution::active()->get(),
            'stats' => [
                'total_synced' => LmsErpSyncLog::where('status', 'completed')->sum('records_processed'),
                'fail_count'   => LmsErpSyncLog::where('status', 'failed')->count(),
                'last_sync'    => LmsErpSyncLog::latest()->value('created_at')
            ]
        ]);
    }

    /**
     * Manual Trigger for User & Enrollment Sync.
     */
    public function sync(Request $request)
    {
        $request->validate([
            'institution_id' => 'required|exists:lms_institutions,id',
            'entity'         => 'required|in:user,enrollment,grade'
        ]);

        // In production, we dispatch a job. For now, we perform incremental sync.
        $result = $this->erpService->performSync(
            $request->institution_id, 
            $request->entity
        );

        return back()->with('success', "Sync initiated: {$result['processed']} records updated.");
    }
}
