<?php

namespace App\Services\Inventory;

use App\Models\InventoryItem;
use App\Models\InventoryTransaction;
use App\Models\PurchaseRequest;
use Illuminate\Support\Facades\DB;
use Exception;

class InventoryService
{
    /**
     * Consume an item (e.g., take 2 packs of paper).
     */
    public function consume(int $itemId, float $quantity, ?int $userId, string $reason = null): InventoryTransaction
    {
        return DB::transaction(function () use ($itemId, $quantity, $userId, $reason) {
            $item = InventoryItem::lockForUpdate()->findOrFail($itemId); // Prevent race condition

            if ($item->current_stock < $quantity) {
                throw new Exception("Insufficient stock. Current: {$item->current_stock}");
            }

            // Deduct Stock
            $item->decrement('current_stock', $quantity);

            // Create consumption Record
            $tx = InventoryTransaction::create([
                'item_id' => $itemId,
                'type' => 'Consumption',
                'quantity' => $quantity,
                'requested_by' => $userId,
                'reason' => $reason
            ]);

            // Check Low Stock & Auto-Draft PO
            $this->checkReorderLevel($item);

            return $tx;
        });
    }

    /**
     * Add stock (Purchase or Return).
     */
    public function addStock(int $itemId, float $quantity, float $unitCost, string $type = 'Purchase'): InventoryTransaction
    {
        return DB::transaction(function () use ($itemId, $quantity, $unitCost, $type) {
            $item = InventoryItem::lockForUpdate()->findOrFail($itemId);

            $item->increment('current_stock', $quantity);

            // Update average cost? (Simple logic for now)

            return InventoryTransaction::create([
                'item_id' => $itemId,
                'type' => $type,
                'quantity' => $quantity,
                'unit_cost' => $unitCost
            ]);
        });
    }

    /**
     * Check if item needs reordering and create Draft Purchase Request.
     */
    public function checkReorderLevel(InventoryItem $item): void
    {
        if ($item->current_stock <= $item->min_stock_level) {
            // Check if there's already a Draft/Ordered request for this item to avoid duplicates?
            // Simplified: Send Notification to Admins
            
            // Find admins (Role based) or just the specific Store Manager
            $admins = \App\Models\User::whereHas('roles', function($q) {
                $q->where('name', 'Super Admin')
                  ->orWhere('name', 'Store Manager');
            })->get();

            \Illuminate\Support\Facades\Notification::send($admins, new \App\Notifications\LowStockNotification($item));
        }
    }

    /**
     * Create a Purchase Request.
     */
    public function createPurchaseRequest(int $tenantId, array $items, int $requestedBy): PurchaseRequest
    {
        // Items: [['item_name' => 'Paper', 'qty' => 10, 'cost' => 50]]
        $total = collect($items)->sum(fn($i) => $i['qty'] * ($i['cost'] ?? 0));

        return PurchaseRequest::create([
            'tenant_id' => $tenantId,
            'status' => 'Draft',
            'items' => $items,
            'total_cost' => $total,
            'created_by' => $requestedBy,
            'expected_date' => now()->addDays(7)
        ]);
    }
}
