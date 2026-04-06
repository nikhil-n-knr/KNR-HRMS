<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\InventoryItem;
use App\Services\Inventory\InventoryService;
use Inertia\Inertia;

class InventoryController extends Controller
{
    protected $service;

    public function __construct(InventoryService $service)
    {
        $this->service = $service;
    }

    public function dashboard(Request $request)
    {
        $view = $request->query('view', 'stats');

        if ($view === 'stats') {
             $lowStockCount = InventoryItem::whereRaw('current_stock <= min_stock_level')->count();
             $totalItems = InventoryItem::count();
             
             // Simple "Consumption Velocity" - items consumed in last 7 days
             $velocity = \App\Models\InventoryTransaction::where('type', 'Consumption')
                ->where('created_at', '>=', now()->subDays(7))
                ->count();

             $alerts = InventoryItem::whereRaw('current_stock <= min_stock_level')
                ->take(5)
                ->get(); // Items that need reordering

             return Inertia::render('Admin/Inventory/SmartIndex', [
                 'tab' => 'stats',
                 'stats' => [
                     'low_stock' => $lowStockCount,
                     'total_items' => $totalItems,
                     'velocity' => $velocity,
                     'alerts' => $alerts
                 ]
             ]);
        }

        if ($view === 'list') {
             return Inertia::render('Admin/Inventory/SmartIndex', [
                 'tab' => 'list',
                 'items' => InventoryItem::latest()->paginate(20)
             ]);
        }

        if ($view === 'procurement') {
            return Inertia::render('Admin/Inventory/SmartIndex', [
                'tab' => 'procurement',
                'procurement' => InventoryItem::whereColumn('current_stock', '<=', 'min_stock_level')
                    ->orderByRaw('(min_stock_level - current_stock) DESC')
                    ->get()
            ]);
        }

        return redirect()->route('admin.inventory.dashboard', ['view' => 'stats']);

    }

    public function index()
    {
        return redirect()->route('admin.inventory.dashboard', ['view' => 'list']);
    }

    public function create() 
    {
         // Simple render of a create view or redirect to a modal-friendly page
         // For now, let's reuse the index but with a 'create' flag or just a dedicated page
         // Since we don't have a dedicated Create.vue yet, let's assume we use a modal on the main page
         // OR we just make a simple Create.vue. 
         // Given spec, let's just create a basic Create view to stop the crash.
         return Inertia::render('Admin/Inventory/Create');
    }

    public function addStock(Request $request, InventoryItem $item)
    {
        $request->validate([
            'quantity' => 'required|numeric|min:1',
            'unit_cost' => 'required|numeric'
        ]);

        $this->service->addStock($item->id, $request->quantity, $request->unit_cost);

        return back()->with('success', 'Stock Added Successfully');
    }

    public function consume(Request $request, InventoryItem $item)
    {
        $request->validate([
            'quantity' => 'required|numeric|min:0.1',
            'reason' => 'nullable|string'
        ]);

        try {
            $this->service->consume($item->id, $request->quantity, auth()->id(), $request->reason);
            return back()->with('success', 'Consumption Logged');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function scanner()
    {
        return Inertia::render('Admin/Inventory/Scanner');
    }

    public function processScan(Request $request)
    {
        $request->validate([
            'barcode' => 'required|string'
        ]);
        
        // Check Asset first
        $asset = \App\Models\Asset::where('serial_number', $request->barcode)->first();

        if ($asset) {
             // Mock "Audit" action - just verifying existence for now
            return back()->with([
                'scan_success' => true,
                'message' => "Verified Asset: {$asset->name}",
                'item' => $asset
            ]);
        }
        
        return back()->with([
            'scan_success' => false,
            'message' => 'Item not found',
        ]);
    }
}
