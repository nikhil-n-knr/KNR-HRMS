<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VaultCategory;
use App\Models\VaultArticle;
use App\Models\Project;
use Illuminate\Http\Request;
use Inertia\Inertia;

class VaultController extends Controller
{
    public function index()
    {
        $categories = VaultCategory::with(['project', 'articles' => function($q) {
            $q->orderBy('order');
        }])->orderBy('order')->get();

        $projects = Project::select('id', 'name')->get();

        return Inertia::render('Admin/Vault/Index', [
            'categories' => $categories,
            'projects' => $projects
        ]);
    }

    public function storeCategory(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'name' => 'required|string|max:255',
            'icon' => 'nullable|string',
            'order' => 'integer|min:0'
        ]);

        VaultCategory::create($validated);

        return redirect()->back()->with('success', 'Category created successfully.');
    }

    public function updateCategory(Request $request, VaultCategory $category)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'name' => 'required|string|max:255',
            'icon' => 'nullable|string',
            'order' => 'integer|min:0'
        ]);

        $category->update($validated);

        return redirect()->back()->with('success', 'Category updated successfully.');
    }

    public function destroyCategory(VaultCategory $category)
    {
        $category->delete();
        return redirect()->back()->with('success', 'Category deleted successfully.');
    }

    public function storeArticle(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:vault_categories,id',
            'title' => 'required|string|max:255',
            'excerpt' => 'nullable|string',
            'content' => 'required|string',
            'is_featured' => 'boolean',
            'is_client_visible' => 'boolean',
            'order' => 'integer|min:0'
        ]);

        VaultArticle::create($validated);

        return redirect()->back()->with('success', 'Article created successfully.');
    }

    public function updateArticle(Request $request, VaultArticle $article)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:vault_categories,id',
            'title' => 'required|string|max:255',
            'excerpt' => 'nullable|string',
            'content' => 'required|string',
            'is_featured' => 'boolean',
            'is_client_visible' => 'boolean',
            'order' => 'integer|min:0'
        ]);

        $article->update($validated);

        return redirect()->back()->with('success', 'Article updated successfully.');
    }

    public function destroyArticle(VaultArticle $article)
    {
        $article->delete();
        return redirect()->back()->with('success', 'Article deleted successfully.');
    }
}
