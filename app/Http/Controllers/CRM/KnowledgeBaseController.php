<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use App\Models\CRM\KbCategory;
use App\Models\CRM\KbArticle;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class KnowledgeBaseController extends Controller
{
    public function categories()
    {
        $tenantId = auth()->user()->tenant_id;
        $categories = KbCategory::where('tenant_id', $tenantId)
            ->withCount('articles')
            ->get();
        return response()->json($categories);
    }

    public function articles(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;
        $query = KbArticle::where('tenant_id', $tenantId)->with('category');

        if ($request->category_id) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->search) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        return response()->json($query->latest()->paginate(20));
    }

    public function storeArticle(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'category_id' => 'required|exists:crm_kb_categories,id',
            'status' => 'required|in:draft,published',
        ]);

        $article = KbArticle::create([
            'tenant_id' => auth()->user()->tenant_id,
            'category_id' => $request->category_id,
            'author_id' => auth()->id(),
            'title' => $request->title,
            'slug' => Str::slug($request->title) . '-' . rand(1000, 9999),
            'content' => $request->content,
            'status' => $request->status,
        ]);

        return response()->json(['message' => 'Article created', 'article' => $article], 201);
    }

    public function showArticle(KbArticle $article)
    {
        if ($article->tenant_id !== auth()->user()->tenant_id) abort(403);
        $article->increment('views');
        return response()->json($article->load('category'));
    }

    public function storeCategory(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $category = KbCategory::create([
            'tenant_id' => auth()->user()->tenant_id,
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
        ]);

        return response()->json($category, 201);
    }
}
