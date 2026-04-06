<?php

namespace App\Services\Assets;

use App\Models\User;
use App\Models\AssetCategory;
use App\Models\AssetAssignment;
use App\Models\AssetRequest;

class AssetPolicyService
{
    /**
     * Check if a user is allowed to request a specific category of asset.
     * Returns true if allowed, or throws an exception/returns error string if blocked.
     */
    public function canRequest(User $user, $categoryId)
    {
        $category = AssetCategory::find($categoryId);
        if (!$category) return "Invalid Category";

        // Rule 1: Max 1 Laptop (assuming category name contains 'Laptop')
        if (stripos($category->name, 'Laptop') !== false) {
            $hasLaptop = AssetAssignment::where('user_id', $user->id)
                ->whereNull('returned_at')
                ->whereHas('asset.category', function($q) {
                    $q->where('name', 'like', '%Laptop%');
                })
                ->exists();

            if ($hasLaptop) {
                return "Policy Violation: You already have an assigned Laptop. Return it before requesting a new one.";
            }
        }

        // Rule 2: No Pending Requests for same category
        $pendingRequest = AssetRequest::where('user_id', $user->id)
            ->where('category_id', $categoryId)
            ->where('status', 'Pending')
            ->exists();

        if ($pendingRequest) {
            return "Policy Violation: You already have a pending request for this item.";
        }

        return true;
    }
}
