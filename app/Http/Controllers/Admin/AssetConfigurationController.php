<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AssetCategory;
use Inertia\Inertia;

class AssetConfigurationController extends Controller
{
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
