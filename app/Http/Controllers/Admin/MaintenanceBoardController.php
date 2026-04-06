<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AssetMaintenanceLog;
use Inertia\Inertia;

class MaintenanceBoardController extends Controller
{
    public function index()
    {
        // Fetch all maintenance logs with their related asset and performer
        $logs = AssetMaintenanceLog::with(['asset.category', 'logger'])
            ->latest('service_date')
            ->get();

        // Group by Type for Kanban-style columns (type col exists; status does not)
        $columns = [
            'Repair'          => $logs->where('type', 'Repair')->values(),
            'Upgrade'         => $logs->where('type', 'Upgrade')->values(),
            'Routine_Service' => $logs->where('type', 'Routine_Service')->values(),
        ];

        return Inertia::render('Admin/Assets/Maintenance/Index', [
            'board' => $columns,
            'stats' => [
                'total_active' => $logs->count(),
                'total_cost'   => $logs->sum('cost')
            ]
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'type' => 'required|string|in:Repair,Upgrade,Routine_Service',
        ]);

        $log = AssetMaintenanceLog::findOrFail($id);
        $log->update(['type' => $request->type]);

        return back()->with('success', 'Maintenance log updated.');
    }
}
