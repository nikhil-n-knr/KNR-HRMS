<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Asset;
use App\Services\Assets\AssetService;
use Inertia\Inertia;

class AssetController extends Controller
{
    protected $service;

    public function __construct(AssetService $service)
    {
        $this->service = $service;
    }

    public function dashboard(Request $request)
    {
        $view = $request->query('view', 'stats'); // Default to Stats

        if ($view === 'stats') {
             $stats = [
                 'total_assets' => Asset::count(),
                 'total_value' => Asset::sum('purchase_cost'),
                 'in_service' => Asset::where('status', 'In_Service')->count(),
                 'assigned' => Asset::where('status', 'Assigned')->count(),
                 'active_vendors' => \App\Models\Vendor::where('is_active', true)->count(),
                 'by_category' => \App\Models\AssetCategory::withCount('assets')->get(),
                 'low_health' => Asset::whereRaw('DATEDIFF(NOW(), purchase_date) > 1095')->count(), // > 3 Years old
                 'recent_maintenance' => \App\Models\AssetMaintenanceLog::latest()->take(5)->with('asset')->get(),
                 'predictive_alerts' => (new \App\Services\Analytics\PredictiveService)->getMaintenanceForecast()->take(5)
             ];
             
             return Inertia::render('Admin/Assets/SmartIndex', [
                 'tab' => 'stats',
                 'stats' => $stats
             ]);
        }
        
        // Master List View
        if ($view === 'list') {
            $query = Asset::with(['category', 'assignments.user']);

            // Filters
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('serial_number', 'like', "%{$search}%");
                });
            }

            if ($request->filled('category_id')) {
                $query->where('category_id', $request->category_id);
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('location_id')) {
                $query->where('location_id', $request->location_id);
            }

            return Inertia::render('Admin/Assets/SmartIndex', [
                'tab' => 'list',
                'assets' => $query->latest()->paginate(10)->withQueryString(),
                'filters' => $request->only(['search', 'category_id', 'status', 'location_id']),
                'categories' => \App\Models\AssetCategory::all(),
                'locations' => \App\Models\Location::all(),
                'vendors' => \App\Models\Vendor::all(), // For Vendor Tab
                'procurement' => \App\Models\PurchaseRequest::with('vendor')->latest()->take(10)->get(), // For Procurement Tab
                'statuses' => ['Available', 'Assigned', 'In_Service', 'Scrapped', 'Lost']
            ]);
        }

        // Config View
        if ($view === 'config') {
             return Inertia::render('Admin/Assets/SmartIndex', [
                'tab' => 'config',
                'categories' => \App\Models\AssetCategory::all()
             ]);
        }

        // Vendors View
        if ($view === 'vendors') {
             return Inertia::render('Admin/Assets/SmartIndex', [
                'tab' => 'vendors',
                'vendors' => \App\Models\Vendor::all(),
                'categories' => \App\Models\AssetCategory::all()
             ]);
        }

        // Procurement View
        if ($view === 'procurement') {
             return Inertia::render('Admin/Assets/SmartIndex', [
                'tab' => 'procurement',
                'procurement' => \App\Models\PurchaseRequest::with('vendor')->latest()->take(20)->get(),
                'vendors' => \App\Models\Vendor::all(),
                'stats' => [ // Minimal stats for sidebar if needed, or null
                    'active_vendors' => \App\Models\Vendor::where('is_active', true)->count()
                ]
             ]);
        }

        // Requests View
        if ($view === 'requests') {
             return Inertia::render('Admin/Assets/SmartIndex', [
                'tab' => 'requests',
                'requests' => \App\Models\AssetRequest::with(['user', 'asset'])->where('status', 'Pending')->latest()->get()
             ]);
        }
        
        return redirect()->route('admin.assets.dashboard', ['view' => 'stats']);
    }

    public function printLabel(Asset $asset)
    {
        // Generate QR Code content (Universal Scan URL)
        $url = route('assets.scan', $asset->id);
        
        // Generate SVG string
        $qrCode = \SimpleSoftwareIO\QrCode\Facades\QrCode::size(150)->generate($url);

        return Inertia::render('Admin/Assets/Label', [
            'asset' => $asset->load('category'),
            'qrCode' => $qrCode
        ]);
    }

    public function index()
    {
        // Legacy Redirect to Smart List
        return redirect()->route('admin.assets.dashboard', ['view' => 'list']);
    }

    public function create()
    {
        return Inertia::render('Admin/Assets/Create', [
            'categories' => \App\Models\AssetCategory::all(),
            'locations' => \App\Models\Location::all()
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'category_id' => 'required|exists:asset_categories,id',
            'serial_number' => 'nullable|unique:assets,serial_number',
            'purchase_cost' => 'nullable|numeric',
            'purchase_date' => 'nullable|date',
            'quantity' => 'nullable|integer|min:1',
            'is_serialized' => 'boolean',
            'make' => 'nullable|string',
            'model' => 'nullable|string'
        ]);

        if (empty($data['is_serialized'])) {
            $data['is_serialized'] = false;
        }

        if ($data['is_serialized'] && empty($data['serial_number'])) {
            return back()->withErrors(['serial_number' => 'Serial Number is required for serialized assets.']);
        }

        $data['tenant_id'] = auth()->user()->tenant_id;
        
        $data['meta'] = json_encode([
            'make' => $data['make'] ?? null,
            'model' => $data['model'] ?? null
        ]);
        
        unset($data['make'], $data['model']);
        
        Asset::create($data);

        return back()->with('success', 'Asset Created Successfully');
    }

    public function assign(Request $request, Asset $asset)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);

        try {
            $this->service->assign($asset->id, $request->user_id, auth()->id());
            return back()->with('success', 'Asset Assigned Successfully');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    // --- Bulk Actions ---

    public function bulkAssign()
    {
        return Inertia::render('Admin/Assets/BulkAssign', [
            'assets' => Asset::where('status', 'Available')->select('id', 'name', 'serial_number', 'category_id')->with('category')->get(),
            'users' => \App\Models\User::select('id', 'name')->orderBy('name')->get()
        ]);
    }

    public function processBulkAssign(Request $request)
    {
        $request->validate([
            'asset_ids' => 'required|array|min:1',
            'asset_ids.*' => 'exists:assets,id',
            'user_id' => 'required|exists:users,id'
        ]);

        $count = 0;
        foreach ($request->asset_ids as $id) {
            try {
                $this->service->assign($id, $request->user_id, auth()->id());
                $count++;
            } catch (\Exception $e) {
                // Continue best effort or stop? Best effort for bulk.
                continue;
            }
        }

        return redirect()->route('admin.assets.index')->with('success', "Successfully assigned {$count} assets.");
    }

    public function return(Request $request, Asset $asset)
    {
        $request->validate([
            'condition' => 'nullable|string',
        ]);

        try {
            // Find active assignment
            $assignment = $asset->assignment;
            if (!$assignment) {
                return back()->with('error', 'Asset is not currently assigned.');
            }

            // Update Assignment
            $assignment->update([
                'returned_at' => now(),
                'condition_on_return' => $request->condition
            ]);

            // Update Asset Status
            $asset->update(['status' => 'Available']);


            return back()->with('success', 'Asset returned successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function storeMaintenance(Request $request, Asset $asset)
    {
        $request->validate([
            'type' => 'required|string', // Repair, Upgrade, Inspection
            'description' => 'required|string',
            'cost' => 'required|numeric|min:0',
            'service_date' => 'required|date'
        ]);

        \App\Models\MaintenanceLog::create([
            'asset_id' => $asset->id,
            'logged_by' => auth()->id(),
            'type' => $request->type,
            'description' => $request->description,
            'cost' => $request->cost,
            'service_date' => $request->service_date,
            'status' => 'Completed' // Simplified for now
        ]);

        // Auto-Integrate with Finance (Expense Module)
        if ($request->cost > 0) {
            \App\Models\Expense::create([
                'title' => 'Asset Maintenance: ' . $asset->name,
                'amount' => $request->cost,
                'currency' => 'USD', // Default
                'incurred_date' => $request->service_date,
                'category' => 'Repairs & Maintenance',
                'status' => 'Pending', // Needs finance approval
                'description' => $request->description . " (Asset ID: {$asset->id})",
                // 'approved_by' => null, 
                // 'employee_id' => auth()->user()->employee->id ?? null // If we map user to employee
            ]);
        }

        return back()->with('success', 'Maintenance Logged Successfully');
    }

    public function show(Asset $asset)
    {
        $asset->load(['category', 'assignment.user', 'maintenanceLogs', 'assignments.user', 'assignments.assignedBy']);

        // Merge Assignments and Maintenance Logs into a single timeline
        $timeline = collect();

        // 1. Assignment Events
        foreach ($asset->assignments as $assignment) {
            $timeline->push([
                'type' => 'assignment',
                'date' => $assignment->assigned_at,
                'title' => 'Assigned to ' . ($assignment->user->name ?? 'Unknown'),
                'description' => 'Assigned by ' . ($assignment->assignedBy->name ?? 'System'),
                'status' => $assignment->ack_status,
                'icon' => 'UserPlusIcon',
                'color' => 'blue'
            ]);

            if ($assignment->returned_at) {
                $timeline->push([
                    'type' => 'return',
                    'date' => $assignment->returned_at,
                    'title' => 'Returned by ' . ($assignment->user->name ?? 'Unknown'),
                    'description' => $assignment->condition_on_return,
                    'status' => 'Returned',
                    'icon' => 'UserMinusIcon',
                    'color' => 'gray'
                ]);
            }
        }

        // 2. Maintenance Events
        foreach ($asset->maintenanceLogs as $log) {
            $timeline->push([
                'type' => 'maintenance',
                'date' => $log->service_date,
                'title' => 'Maintenance: ' . $log->type,
                'description' => $log->description . ' (Cost: $' . $log->cost . ')',
                'status' => 'Completed',
                'icon' => 'WrenchIcon',
                'color' => 'amber'
            ]);
        }

        // 3. Purchase Event (Creation)
        if ($asset->purchase_date) {
            $timeline->push([
                'type' => 'purchase',
                'date' => $asset->purchase_date,
                'title' => 'Asset Purchased',
                'description' => 'Original Cost: $' . $asset->purchase_cost,
                'status' => 'New',
                'icon' => 'ShoppingBagIcon',
                'color' => 'emerald'
            ]);
        }

        // Sort by Date Descending
        $timeline = $timeline->sortByDesc('date')->values();

        // Generate QR for the view as well
        $qrCode = \SimpleSoftwareIO\QrCode\Facades\QrCode::size(100)->generate(route('admin.assets.show', $asset->id));

        return Inertia::render('Admin/Assets/Show', [
            'asset' => $asset,
            'timeline' => $timeline,
            'qrCode' => $qrCode
        ]);
    }

    // --- Employee Self-Service Methods ---

    public function myAssets()
    {
        $user = auth()->user();
        
        // Fetch assignments for this user
        // We assume 'ack_status' tracks acceptance (Pending/Accepted)
        // And 'returned_at' being null means it's currently with them.
        
        $assignments = \App\Models\AssetAssignment::with(['asset.category'])
            ->where('user_id', $user->id)
            ->whereNull('returned_at')
            ->get();

        $pending = $assignments->where('ack_status', 'Pending')->pluck('asset');
        $active = $assignments->where('ack_status', 'Accepted')->pluck('asset');

        return Inertia::render('Employee/Assets/MyAssets', [
            'assets' => [
                'pending' => $pending->values(),
                'active' => $active->values()
            ],
            'user' => $user
        ]);
    }

    public function acceptAsset(Request $request, Asset $asset)
    {
        // Find the pending assignment
        $assignment = \App\Models\AssetAssignment::where('asset_id', $asset->id)
            ->where('user_id', auth()->id())
            ->whereNull('returned_at')
            ->where('ack_status', 'Pending')
            ->firstOrFail();

        $assignment->update([
            'ack_status' => 'Accepted',
            'ack_date' => now()
        ]);

        // Updates asset status to 'Deployed' if not already
        $asset->update(['status' => 'Deployed']);

        return back()->with('success', 'You have accepted custody of the asset.');
    }

    public function requestReturn(Request $request, Asset $asset)
    {
        $request->validate([
            'reason' => 'required|string',
            'condition' => 'required|string',
            'notes' => 'nullable|string'
        ]);

        // Logic: Create a "Return Request" record or Flag the Assignment
        // For simplicity, we'll create an AssetRequest of type 'Return'
        
        \App\Models\AssetRequest::create([
            'user_id' => auth()->id(),
            'asset_id' => $asset->id,
            'type' => 'Return',
            'reason' => $request->reason,
            'status' => 'Pending',
            'notes' => "Condition: {$request->condition}. " . $request->notes,
            'request_date' => now()
        ]);

        return back()->with('success', 'Return request submitted. Admin will verify handover.');
    }
}
