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
             $search = $request->input('search');
             $query = InventoryItem::latest();
             if ($search) {
                 $query->where(function($q) use ($search) {
                     $q->where('name', 'like', "%{$search}%")
                       ->orWhere('sku', 'like', "%{$search}%")
                       ->orWhere('category', 'like', "%{$search}%");
                 });
             }
             return Inertia::render('Admin/Inventory/SmartIndex', [
                 'tab' => 'list',
                 'items' => $query->paginate(20)->withQueryString()
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

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:100|unique:inventory_items,sku',
            'category' => 'nullable|string|max:100',
            'min_stock_level' => 'required|numeric|min:0',
            'current_stock' => 'required|numeric|min:0',
            'unit_cost' => 'required|numeric|min:0',
            'unit' => 'required|string|max:50'
        ]);

        $item = InventoryItem::create($validated);

        \App\Services\Infrastructure\LoggerService::info("Inventory item created", [
            'item_id' => $item->id,
            'name' => $item->name,
            'action_by' => auth()->id()
        ]);

        return to_route('admin.inventory.dashboard', ['view' => 'list'])
            ->with('success', 'Resource Registered Successfully')
            ->setStatusCode(303);
    }

    public function show(InventoryItem $item)
    {
        return Inertia::render('Admin/Inventory/Show', [
            'item' => $item->load('transactions.requestedBy')
        ]);
    }

    public function update(Request $request, InventoryItem $item)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:100|unique:inventory_items,sku,' . $item->id,
            'category' => 'nullable|string|max:100',
            'min_stock_level' => 'required|numeric|min:0',
            'current_stock' => 'required|numeric|min:0',
            'unit_cost' => 'required|numeric|min:0',
            'unit' => 'required|string|max:50'
        ]);

        $item->update($validated);

        \App\Services\Infrastructure\LoggerService::info("Inventory item updated", [
            'item_id' => $item->id,
            'changes' => $item->getChanges(),
            'action_by' => auth()->id()
        ]);

        return back()->with('success', 'Resource Updated Successfully')
            ->setStatusCode(303);
    }

    public function destroy(InventoryItem $item)
    {
        $itemId = $item->id;
        $itemName = $item->name;
        
        $item->delete();

        \App\Services\Infrastructure\LoggerService::info("Inventory item deleted", [
            'item_id' => $itemId,
            'name' => $itemName,
            'action_by' => auth()->id()
        ]);

        return back()->with('success', 'Resource purged from registry')
            ->setStatusCode(303);
    }

    public function addStock(Request $request, InventoryItem $item)
    {
        $request->validate([
            'quantity' => 'required|numeric|min:1',
            'unit_cost' => 'required|numeric'
        ]);

        $this->service->addStock($item->id, $request->quantity, $request->unit_cost);

        \App\Services\Infrastructure\LoggerService::info("Inventory stock added", [
            'item_id' => $item->id,
            'quantity' => $request->quantity,
            'action_by' => auth()->id()
        ]);

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
            
            \App\Services\Infrastructure\LoggerService::info("Inventory consumed", [
                'item_id' => $item->id,
                'quantity' => $request->quantity,
                'action_by' => auth()->id()
            ]);

            return back()->with('success', 'Consumption Logged');
        } catch (\Exception $e) {
            \App\Services\Infrastructure\LoggerService::error("Inventory consumption failed", [
                'item_id' => $item->id,
                'quantity' => $request->quantity,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
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
        
        // Check Inventory first, then Asset
        $item = InventoryItem::where('sku', $request->barcode)->first();
        if ($item) {
            return back()->with([
                'scan_success' => true,
                'message' => "Verified Hub Unit: {$item->name}",
                'item' => $item
            ]);
        }

        $asset = \App\Models\Asset::where('serial_number', $request->barcode)->first();

        if ($asset) {
            return back()->with([
                'scan_success' => true,
                'message' => "Verified Asset: {$asset->name}",
                'item' => $asset
            ]);
        }
        
        return back()->with([
            'scan_success' => false,
            'message' => 'Resource not found in registry',
        ]);
    }
}
