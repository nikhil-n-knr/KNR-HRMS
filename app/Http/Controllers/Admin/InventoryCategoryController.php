<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryCategory;
use Illuminate\Http\Request;

class InventoryCategoryController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = optional($request->user())->tenant_id;

        $categories = InventoryCategory::query()
            ->with('children')
            ->withCount('items')
            ->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))
            ->whereNull('parent_id')
            ->orderBy('name')
            ->get();

        return response()->json(['data' => $categories]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'parent_id' => 'nullable|exists:inventory_categories,id',
            'name' => 'required|string|max:120',
            'code' => 'nullable|string|max:40',
            'item_type' => 'required|in:Consumable,Non_Consumable,Serviceable',
        ]);

        $validated['tenant_id'] = optional($request->user())->tenant_id;

        $category = InventoryCategory::create($validated);

        return response()->json(['message' => 'Category created.', 'data' => $category], 201);
    }

    public function update(Request $request, InventoryCategory $category)
    {
        $validated = $request->validate([
            'parent_id' => 'nullable|exists:inventory_categories,id',
            'name' => 'required|string|max:120',
            'code' => 'nullable|string|max:40',
            'item_type' => 'required|in:Consumable,Non_Consumable,Serviceable',
        ]);

        $category->update($validated);

        return response()->json(['message' => 'Category updated.', 'data' => $category->fresh()]);
    }

    public function destroy(InventoryCategory $category)
    {
        if ($category->children()->exists()) {
            return response()->json(['message' => 'Cannot delete category with subcategories.'], 422);
        }

        if ($category->items()->exists()) {
            return response()->json(['message' => 'Cannot delete category mapped to items.'], 422);
        }

        $category->delete();

        return response()->json(['message' => 'Category deleted.']);
    }
}
