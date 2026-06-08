<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Asset;
use App\Models\InventoryItem;
use App\Models\AssetMaintenanceLog;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class AssetAnalyticsController extends Controller
{
    public function index()
    {
        // 1. Asset Stats
        $totalAssets = Asset::count();
        $assignedAssets = Asset::where('status', 'Assigned')->count();
        $inServiceAssets = Asset::where('status', 'In_Service')->count();
        $totalValue = Asset::sum('purchase_cost');

        // 2. Inventory Stats
        $lowStockItems = InventoryItem::whereColumn('current_stock', '<=', 'min_stock_level')->get();
        $totalInventoryValue = DB::table('inventory_items')
            ->join('inventory_transactions', 'inventory_items.id', '=', 'inventory_transactions.item_id')
            ->where('inventory_transactions.type', 'Purchase')
            ->sum(DB::raw('inventory_transactions.quantity * inventory_transactions.unit_cost'));

        // 3. Maintenance Costs (Last 6 Months)
        $maintenanceCosts = AssetMaintenanceLog::select(
            DB::raw('DATE_FORMAT(service_date, "%Y-%m") as month'),
            DB::raw('SUM(cost) as total_cost')
        )
        ->groupBy('month')
        ->orderBy('month', 'desc')
        ->limit(6)
        ->get();

        return Inertia::render('Admin/Assets/Dashboard', [
            'stats' => [
                'total_assets' => $totalAssets,
                'assigned_rate' => $totalAssets > 0 ? round(($assignedAssets / $totalAssets) * 100) : 0,
                'in_service' => $inServiceAssets,
                'total_asset_value' => $totalValue,
                'low_stock_count' => $lowStockItems->count(),
            ],
            'low_stock_items' => $lowStockItems,
            'maintenance_trend' => $maintenanceCosts
        ]);
    }
}
