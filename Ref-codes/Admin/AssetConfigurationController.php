<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AssetCategory;
use Inertia\Inertia;

class AssetConfigurationController extends Controller
{
    public function storeCategory(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'is_electronic' => 'nullable|boolean',
        ]);

        $data['is_electronic'] = (bool) ($data['is_electronic'] ?? false);

        AssetCategory::create($data);

        return back()->with('success', 'Category Created');
    }

    public function updateCategoryBasics(Request $request, AssetCategory $category)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'is_electronic' => 'nullable|boolean',
        ]);

        $data['is_electronic'] = (bool) ($data['is_electronic'] ?? false);

        $category->update($data);

        return back()->with('success', 'Category Updated');
    }

    public function destroyCategory(AssetCategory $category)
    {
        if ($category->assets()->exists()) {
            return back()->with('error', 'Cannot delete category with linked assets.');
        }

        $category->delete();

        return back()->with('success', 'Category Deleted');
    }

    public function index()
    {
        $categories = AssetCategory::all();

        // Placeholder for other configs (Workflows, Vendors, etc.)
        // In a real app, these would come from 'settings' table or specific tables.
        $configurations = [
            'workflows' => [
                ['role' => 'Manager', 'limit' => 500, 'requires_approval' => true],
                ['role' => 'Director', 'limit' => 5000, 'requires_approval' => false]
            ],
            'depreciation_defaults' => [
                'method' => 'Straight Line',
                'useful_life' => 5
            ]
        ];

        return Inertia::render('Admin/Assets/Configurations', [
            'categories' => $categories,
            'initialConfigs' => $configurations
        ]);
    }

    public function updateCategory(Request $request, AssetCategory $category)
    {
        $data = $request->validate([
            'custom_attributes' => 'nullable|array',
            'useful_life_years' => 'nullable|integer',
            'depreciation_method' => 'nullable|string'
        ]);

        $category->update($data);

        return back()->with('success', 'Category Configuration Updated');
    }
}
