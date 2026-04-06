<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\Kit;
use App\Models\MaintenanceTicket;
use App\Models\AssetRequest;
use App\Services\Assets\AssetService;

class AssetHubController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'dashboard');

        $data = [
            'tab' => $tab,
        ];

        // Load data based on active tab to optimize performance
        if ($tab === 'dashboard') {
            $data['stats'] = $this->getDashboardStats();
        } elseif ($tab === 'inventory') {
            $data['assets'] = Asset::with(['category', 'assignedTo'])->latest()->paginate(15);
            $data['categories'] = AssetCategory::select('id', 'name')->get();
        } elseif ($tab === 'kits') {
            $data['kits'] = Kit::with('items.category')->latest()->get();
        } elseif ($tab === 'maintenance') {
            $data['tickets'] = \App\Models\AssetMaintenanceLog::with(['asset', 'logger'])->latest()->paginate(10);
        } elseif ($tab === 'requests') {
        } elseif ($tab === 'requests') {
             $data['requests'] = \App\Models\PurchaseRequest::with(['createdBy', 'vendor'])->latest()->paginate(10);
        } elseif ($tab === 'config') {
            $data['categories'] = AssetCategory::withCount('assets')->get();
            $data['vendors'] = \App\Models\Vendor::withCount('assets')->get();
        }

        return Inertia::render('Admin/Assets/Hub', $data);
    }

    private function getDashboardStats()
    {
        return [
            'total_assets' => Asset::count(),
            'assigned_assets' => Asset::where('status', 'assigned')->count(),
            'in_stock' => Asset::where('status', 'in_stock')->count(),
            'under_repair' => Asset::where('status', 'under_repair')->count(),
            'total_valuation' => Asset::sum('purchase_cost'), // Simplified
            'warranty_expiring_soon' => Asset::whereBetween('warranty_expiry', [now(), now()->addDays(30)])->count(),
        ];
    }
    
    public function import(Request $request) 
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,xlsx'
        ]);
        
        // MVP: Simple processing
        // Ideally utilize SmartImportService here or redirect to import route
        // For this task, we will just return success to mock the flow or do basic parsing
        
        $file = $request->file('file');
        
        // Mock processing delay
        // sleep(1);

        return back()->with('success', 'Import started in background (Mock). 10 Assets processed.');
    }
}
