<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use App\Models\CRM\Product;
use App\Models\CRM\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Product::where('tenant_id', auth()->user()->tenant_id)
            ->with(['category', 'variants']);

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        return inertia('CRM/Sections/config/Products', [
            'products' => $query->latest()->paginate(50),
            'categories' => ProductCategory::where('tenant_id', auth()->user()->tenant_id)->get()->toTree(), // Assuming nesting trait or simple fetch
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|max:100|unique:crm_products,sku,NULL,id,tenant_id,' . auth()->user()->tenant_id,
            'category_id' => 'nullable|exists:crm_product_categories,id',
            'type' => 'required|in:physical,service,digital',
            'base_price' => 'required|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'stock_qty' => 'integer|min:0',
            'variants' => 'nullable|array',
            'images' => 'nullable|array', // Array of paths or files
        ]);

        DB::beginTransaction();
        try {
            $product = Product::create([
                'tenant_id' => auth()->user()->tenant_id,
                'created_by' => auth()->id(),
                ...$request->except(['variants', 'images']),
                'images' => $request->images, // Handle upload separately usually
            ]);

            if (!empty($request->variants)) {
                foreach ($request->variants as $variant) {
                    $product->variants()->create($variant);
                }
            }

            DB::commit();
            return redirect()->back()->with('success', 'Product created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to create product: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        $this->authorizeAccess($product);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|max:100|unique:crm_products,sku,' . $product->id . ',id,tenant_id,' . auth()->user()->tenant_id,
            'base_price' => 'required|numeric|min:0',
            // Add other validations as needed
        ]);

        DB::beginTransaction();
        try {
            $product->update($request->except(['variants']));

            // Simple variant sync strategy: delete all and recreate, or update by ID. 
            // For MVP/Demo:
            if ($request->has('variants')) {
                $product->variants()->delete();
                foreach ($request->variants as $variant) {
                    $product->variants()->create($variant);
                }
            }

            DB::commit();
            return redirect()->back()->with('success', 'Product updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to update product.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        $this->authorizeAccess($product);
        $product->delete();
        return redirect()->back()->with('success', 'Product deleted successfully.');
    }

    // Helper for authorization
    protected function authorizeAccess($model)
    {
        if ($model->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }
    }

    // API specific for Tree View if needed
    public function tree()
    {
        // Return hierarchy
        return ProductCategory::where('tenant_id', auth()->user()->tenant_id)
            ->with(['children', 'products'])
            ->whereNull('parent_id')
            ->get();
    }
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:10240', // 10MB
        ]);

        $file = $request->file('file');
        $handle = fopen($file->getPathname(), 'r');
        $header = fgetcsv($handle); // Assuming first row is header
        
        // Map header to index
        $map = array_flip(array_map('strtolower', $header));
        
        // Required columns check
        if (!isset($map['name']) || !isset($map['sku'])) {
            return redirect()->back()->with('error', 'CSV must contain "Name" and "SKU" columns.');
        }

        DB::beginTransaction();
        try {
            $tenantId = auth()->user()->tenant_id;
            $count = 0;

            while (($row = fgetcsv($handle)) !== false) {
                // Skip empty rows
                if (empty(array_filter($row))) continue;

                $data = [];
                foreach ($map as $key => $index) {
                    $data[$key] = $row[$index] ?? null;
                }

                // Handle Category (Find or Create)
                $categoryId = null;
                if (!empty($data['category'])) {
                    $category = ProductCategory::firstOrCreate(
                        ['tenant_id' => $tenantId, 'name' => trim($data['category'])],
                        ['description' => 'Imported via CSV']
                    );
                    $categoryId = $category->id;
                }

                // Create/Update Product
                Product::updateOrCreate(
                    ['tenant_id' => $tenantId, 'sku' => $data['sku']],
                    [
                        'name' => $data['name'],
                        'category_id' => $categoryId,
                        'type' => strtolower($data['type'] ?? 'physical'),
                        'base_price' => (float) ($data['price'] ?? 0),
                        'cost_price' => (float) ($data['cost'] ?? 0),
                        'stock_qty' => (int) ($data['stock'] ?? 0),
                        'description' => $data['description'] ?? '',
                        'created_by' => auth()->id(),
                    ]
                );
                $count++;
            }
            
            fclose($handle);
            DB::commit();
            return redirect()->back()->with('success', "Successfully imported {$count} products.");

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }
}
