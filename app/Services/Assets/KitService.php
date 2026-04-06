<?php

namespace App\Services\Assets;

use App\Models\Kit;
use App\Models\Asset;
use App\Models\AssetAssignment;
use Illuminate\Support\Facades\DB;
use App\Services\Assets\AssetService;

class KitService
{
    protected $assetService;

    public function __construct(AssetService $assetService)
    {
        $this->assetService = $assetService;
    }

    public function createKit($data, $items)
    {
        return DB::transaction(function () use ($data, $items) {
            $kit = Kit::create($data);
            
            foreach ($items as $item) {
                $kit->items()->create([
                    'asset_category_id' => $item['category_id'],
                    'quantity' => $item['quantity']
                ]);
            }
            return $kit;
        });
    }

    public function assignKit($kitId, $userId, $assignedBy)
    {
        $kit = Kit::with('items')->findOrFail($kitId);

        return DB::transaction(function () use ($kit, $userId, $assignedBy) {
            $assignedAssets = [];

            foreach ($kit->items as $item) {
                // Find available assets of this category
                $assets = Asset::where('category_id', $item->asset_category_id)
                    ->where('status', 'Available')
                    ->limit($item->quantity)
                    ->lockForUpdate()
                    ->get();

                if ($assets->count() < $item->quantity) {
                    throw new \Exception("Insufficient stock for category: " . $item->asset_category_id);
                }

                foreach ($assets as $asset) {
                    $this->assetService->assign($asset->id, $userId, $assignedBy);
                    $assignedAssets[] = $asset;
                }
            }

            return $assignedAssets;
        });
    }

    public function autoAssignByDesignation($employee, $assignedBy)
    {
        // Find kit matching designation
        $kit = Kit::where('name', $employee->designation)->orWhere('name', 'like', "%{$employee->designation}%")->first();
        
        if ($kit && $employee->user_id) {
            try {
                $this->assignKit($kit->id, $employee->user_id, $assignedBy);
                return "Auto-assigned kit: {$kit->name}";
            } catch (\Exception $e) {
                return "Failed to auto-assign kit: " . $e->getMessage();
            }
        }
        return null;
    }
}
