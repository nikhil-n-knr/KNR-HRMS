<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AssetCategory;

class AssetCategoryController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:asset_categories,id',
            'code' => 'nullable|string|max:100',
            'is_electronic' => 'boolean',
            'maintenance_interval_days' => 'nullable|integer'
        ]);

        $validated['tenant_id'] = auth()->user()->tenant_id;

        AssetCategory::create($validated);

        return back()->with('success', 'Classification created successfully');
    }

    public function update(Request $request, AssetCategory $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:asset_categories,id',
            'code' => 'nullable|string|max:100',
            'is_electronic' => 'boolean',
            'maintenance_interval_days' => 'nullable|integer'
        ]);

        $category->update($validated);

        return back()->with('success', 'Classification updated successfully');
    }

    public function destroy(AssetCategory $category)
    {
        if ($category->assets()->exists()) {
            return back()->with('error', 'Cannot archive a classification with linked assets.');
        }

        $category->delete(); // Soft delete

        return back()->with('success', 'Classification archived successfully');
    }
}
