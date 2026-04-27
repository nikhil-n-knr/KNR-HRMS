<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AssetMaintenanceLog;
use Inertia\Inertia;

class MaintenanceBoardController extends Controller
{
    public function index(Request $request)
    {
        $query = AssetMaintenanceLog::with(['asset.category', 'asset.location', 'logger']);

        // Search Filter (Asset Name or Serial)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('asset', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('serial_number', 'like', "%{$search}%");
            });
        }

        // Category Filter
        if ($request->filled('category_id')) {
            $query->whereHas('asset', function ($q) use ($request) {
                $q->where('category_id', $request->category_id);
            });
        }

        // Location Filter
        if ($request->filled('location_id')) {
            $query->whereHas('asset', function ($q) use ($request) {
                $q->where('location_id', $request->location_id);
            });
        }

        $logs = $query->latest('service_date')->get();

        // Group by Type for Kanban-style columns
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
            ],
            'filters' => $request->only(['search', 'category_id', 'location_id']),
            'categories' => \App\Models\AssetCategory::all(['id', 'name']),
            'locations' => \App\Models\Location::all(['id', 'name'])
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $type = $request->input('type', $request->input('status'));

        validator(['type' => $type], [
            'type' => 'required|string|in:Repair,Upgrade,Routine_Service',
        ])->validate();

        $log = AssetMaintenanceLog::findOrFail($id);
        $log->update(['type' => $type]);

        return back()->with('success', 'Maintenance log updated.');
    }
}
