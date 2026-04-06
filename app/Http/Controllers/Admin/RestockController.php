<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\InventoryItem;
use Inertia\Inertia;

class RestockController extends Controller
{
    /**
     * Display items that need restocking.
     */
    public function index()
    {
        // Simple Logic: Current Stock <= Min Stock Level
        $shortageItems = InventoryItem::whereColumn('current_stock', '<=', 'min_stock_level')
            ->orderBy('category')
            ->get();

        return Inertia::render('Admin/Store/Procurement/Restock', [
            'shortage_items' => $shortageItems
        ]);
    }

    /**
     * Generate PO for selected items.
     */
    public function createPO(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:inventory_items,id',
            'items.*.order_qty' => 'required|numeric|min:1'
        ]);

        // In a real app, we would:
        // 1. Group by Vendor
        // 2. Create 'PurchaseOrder' record
        // 3. Create 'PurchaseOrderItem' records
        // 4. Generate PDF
        
        // For MVP, we simulate this workflow:
        $count = count($request->items);
        $totalCost = 0;

        foreach ($request->items as $poItem) {
            $dbItem = InventoryItem::find($poItem['id']);
            $totalCost += ($dbItem->unit_cost * $poItem['order_qty']);
        }

        return back()->with('success', "Purchase Order Generated for {$count} items. Est Cost: $" . number_format($totalCost, 2));
    }
}
