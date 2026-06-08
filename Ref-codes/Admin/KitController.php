<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Kit;
use App\Models\User;
use App\Models\AssetCategory;
use App\Services\Assets\KitService;
use Inertia\Inertia;

class KitController extends Controller
{
    protected $service;

    public function __construct(KitService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        return Inertia::render('Admin/Kits/Index', [
            'kits' => Kit::with('items.category')->latest()->paginate(10),
            'categories' => AssetCategory::all()
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'items' => 'required|array|min:1',
            'items.*.category_id' => 'required|exists:asset_categories,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        $this->service->createKit(
            $request->only(['name', 'description']),
            $request->items
        );

        return back()->with('success', 'Kit Created Successfully');
    }

    public function assign(Request $request, Kit $kit)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);

        try {
            $this->service->assignKit($kit->id, $request->user_id, auth()->id());
            return back()->with('success', "Kit '{$kit->name}' assigned successfully!");
        } catch (\Exception $e) {
            return back()->with('error', 'Assignment Failed: ' . $e->getMessage());
        }
    }
}
