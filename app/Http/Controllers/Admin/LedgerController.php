<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class LedgerController extends Controller
{
    public function index(Request $request)
    {
        // 1. Asset Purchases
        $purchases = DB::table('assets')
            ->select(
                DB::raw("'Purchase' as type"),
                DB::raw("purchase_date as date"),
                'name as description',
                'purchase_cost as amount',
                DB::raw("'text-emerald-600' as color"),
                'created_at',
                'id as reference_id'
            )
            ->whereNotNull('purchase_cost');

        // 2. Asset Maintenance (Repairs)
        $repairs = DB::table('maintenance_logs')
            ->join('assets', 'maintenance_logs.asset_id', '=', 'assets.id')
            ->select(
                DB::raw("'Maintenance' as type"),
                'maintenance_logs.service_date as date', 
                DB::raw("CONCAT('Repair: ', assets.name) as description"),
                'maintenance_logs.cost as amount',
                DB::raw("'text-amber-600' as color"),
                'maintenance_logs.created_at',
                'maintenance_logs.id as reference_id'
            )
            ->whereNotNull('maintenance_logs.cost');

        // 3. Inventory Consumption (Usage)
        // We need to calculate Cost = Qty * UnitCost. 
        // InventoryTransaction has qty. We need to join InventoryItem for name, but use transaction for cost (historical).
        $consumption = DB::table('inventory_transactions')
            ->join('inventory_items', 'inventory_transactions.item_id', '=', 'inventory_items.id')
            ->where('inventory_transactions.type', 'Consumption')
            ->select(
                DB::raw("'Consumption' as type"),
                'inventory_transactions.created_at as date',
                DB::raw("CONCAT('Used: ', inventory_items.name) as description"),
                DB::raw("(inventory_transactions.quantity * COALESCE(inventory_transactions.unit_cost, 0)) as amount"),
                DB::raw("'text-blue-600' as color"),
                'inventory_transactions.created_at',
                'inventory_transactions.id as reference_id'
            );

        // Union All
        $query = $purchases->unionAll($repairs)->unionAll($consumption);

        // Filters
        // (For MVP, we just grab latest 100 to avoid complex Union filtering issues)
        $ledger = $query->orderBy('date', 'desc')
                        ->limit(100)
                        ->get();

        // Calculate Totals for Charts/KPIs
        $totalSpend = $ledger->sum('amount');

        return Inertia::render('Admin/Finance/History', [
            'ledger' => $ledger,
            'stats' => [
                'total_spend' => $totalSpend,
                'count' => $ledger->count()
            ]
        ]);
    }
}
