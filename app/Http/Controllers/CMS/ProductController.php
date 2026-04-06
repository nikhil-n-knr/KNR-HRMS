<?php

namespace App\Http\Controllers\CMS;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    protected function tenantId(): int { return auth()->user()->tenant_id; }

    protected function activeSiteId(Request $request): ?int
    {
        return $request->get('site_id') ?? DB::table('cms_sites')
            ->where('tenant_id', $this->tenantId())
            ->where('type', 'ecommerce')
            ->value('id');
    }

    public function index(Request $request)
    {
        $siteId = $this->activeSiteId($request);
        $query  = DB::table('cms_products')
            ->where('tenant_id', $this->tenantId())
            ->when($siteId, fn($q) => $q->where('site_id', $siteId))
            ->when($request->search, fn($q) => $q->where('name', 'like', '%'.$request->search.'%'))
            ->when($request->category_id, fn($q) => $q->where('category_id', $request->category_id))
            ->when($request->filled('active'), fn($q) => $q->where('is_active', $request->active))
            ->orderByDesc('created_at');

        return response()->json($query->paginate(50));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'site_id'          => 'nullable|integer',
            'category_id'      => 'nullable|integer',
            'name'             => 'required|string|max:255',
            'slug'             => 'nullable|string|max:255',
            'description'      => 'nullable|string',
            'short_description'=> 'nullable|string|max:500',
            'price'            => 'required|numeric|min:0',
            'mrp'              => 'nullable|numeric|min:0',
            'discount_pct'     => 'nullable|numeric|min:0|max:100',
            'tax_class'        => 'nullable|string',
            'sku'              => 'nullable|string|max:100',
            'barcode'          => 'nullable|string|max:100',
            'weight'           => 'nullable|numeric',
            'dimensions'       => 'nullable|array',
            'stock_qty'        => 'nullable|integer|min:0',
            'track_inventory'  => 'nullable|boolean',
            'low_stock_threshold' => 'nullable|integer|min:0',
            'allow_backorder'  => 'nullable|boolean',
            'images'           => 'nullable|array',
            'variants'         => 'nullable|array',
            'variant_attributes' => 'nullable|array',
            'seo'              => 'nullable|array',
            'featured'         => 'nullable|boolean',
            'is_active'        => 'nullable|boolean',
            'sort_order'       => 'nullable|integer',
            'discount_starts_at' => 'nullable|date',
            'discount_ends_at'   => 'nullable|date',
        ]);

        $data['tenant_id']   = $this->tenantId();
        $data['site_id']   ??= $this->activeSiteId($request);
        $data['slug']        = $data['slug'] ?? Str::slug($data['name']);
        $data['is_active'] ??= true;
        $data['track_inventory'] ??= true;
        $data['stock_qty']  ??= 0;
        $data['tax_class']  ??= 'GST_18';
        $data['discount_pct'] ??= ($data['mrp'] && $data['price'] < $data['mrp']
            ? round((1 - $data['price'] / $data['mrp']) * 100, 2) : 0);

        // Encode JSON columns
        foreach (['dimensions', 'images', 'variants', 'variant_attributes', 'seo', 'related_products'] as $col) {
            if (isset($data[$col])) $data[$col] = json_encode($data[$col]);
        }

        $data['created_at'] = now();
        $data['updated_at'] = now();

        $id = DB::table('cms_products')->insertGetId($data);

        // Sync to crm_products if exists
        $this->syncToCrm($id, $data);

        return response()->json(DB::table('cms_products')->find($id), 201);
    }

    public function show(string $id)
    {
        $product = DB::table('cms_products')->where('tenant_id', $this->tenantId())->where('id', $id)->first();
        abort_if(!$product, 404);
        return response()->json($product);
    }

    public function update(Request $request, string $id)
    {
        $product = DB::table('cms_products')->where('tenant_id', $this->tenantId())->where('id', $id)->first();
        abort_if(!$product, 404);

        $data = $request->only([
            'name','slug','description','short_description','price','mrp','discount_pct',
            'tax_class','sku','barcode','weight','dimensions','stock_qty','track_inventory',
            'low_stock_threshold','allow_backorder','images','variants','variant_attributes',
            'seo','featured','is_active','sort_order','category_id',
            'discount_starts_at', 'discount_ends_at',
        ]);

        foreach (['dimensions', 'images', 'variants', 'variant_attributes', 'seo'] as $col) {
            if (isset($data[$col]) && is_array($data[$col])) $data[$col] = json_encode($data[$col]);
        }

        if (isset($data['name']) && !isset($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $data['updated_at'] = now();
        DB::table('cms_products')->where('id', $id)->update($data);

        return response()->json(DB::table('cms_products')->find($id));
    }

    public function destroy(string $id)
    {
        DB::table('cms_products')->where('tenant_id', $this->tenantId())->where('id', $id)
            ->update(['deleted_at' => now()]);
        return response()->json(['message' => 'Product deleted.']);
    }

    public function toggle(string $id)
    {
        $product = DB::table('cms_products')->where('tenant_id', $this->tenantId())->where('id', $id)->first();
        abort_if(!$product, 404);
        DB::table('cms_products')->where('id', $id)->update(['is_active' => !$product->is_active, 'updated_at' => now()]);
        return response()->json(['is_active' => !$product->is_active]);
    }

    public function bulkAction(Request $request)
    {
        $data = $request->validate([
            'action' => 'required|string',
            'ids'    => 'required|array',
            'value'  => 'nullable',
            'type'   => 'nullable|string',
            'starts_at' => 'nullable|date',
            'ends_at'   => 'nullable|date',
        ]);

        $query = DB::table('cms_products')->where('tenant_id', $this->tenantId())->whereIn('id', $data['ids']);

        match ($data['action']) {
            'activate'   => $query->update(['is_active' => true, 'updated_at' => now()]),
            'deactivate' => $query->update(['is_active' => false, 'updated_at' => now()]),
            'delete'     => $query->update(['deleted_at' => now()]),
            'apply_discount' => $this->handleBulkDiscount($query, $data),
            default => null
        };

        return response()->json(['message' => 'Bulk action completed.']);
    }

    protected function handleBulkDiscount($query, $data)
    {
        $products = $query->get();
        foreach ($products as $p) {
            $mrp = $p->mrp ?? $p->price;
            $newPrice = $p->price;
            $discountPct = $p->discount_pct;

            if ($data['type'] === 'pct') {
                $discountPct = (float) $data['value'];
                $newPrice = round($mrp * (1 - ($discountPct / 100)), 2);
            } else {
                $discountValue = (float) $data['value'];
                $newPrice = max(0, $mrp - $discountValue);
                $discountPct = round((1 - ($newPrice / $mrp)) * 100, 2);
            }

            DB::table('cms_products')->where('id', $p->id)->update([
                'price' => $newPrice,
                'mrp' => $mrp,
                'discount_pct' => $discountPct,
                'discount_starts_at' => $data['starts_at'] ?? null,
                'discount_ends_at' => $data['ends_at'] ?? null,
                'updated_at' => now()
            ]);
        }
    }

    public function import(Request $request)
    {
        $request->validate(['products' => 'required|array']);
        $tenantId = $this->tenantId();
        $siteId   = $this->activeSiteId($request);
        $imported = 0;

        foreach ($request->products as $p) {
            if (empty($p['name'])) continue;
            
            $insert = [
                'tenant_id' => $tenantId,
                'site_id'   => $siteId,
                'name'      => $p['name'],
                'slug'      => Str::slug($p['name']) . '-' . Str::random(4),
                'sku'       => $p['sku'] ?? null,
                'price'     => $p['price'] ?? 0,
                'mrp'       => $p['mrp'] ?? null,
                'stock_qty' => $p['stock_qty'] ?? 0,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            DB::table('cms_products')->insert($insert);
            $imported++;
        }

        return response()->json(['message' => "Imported $imported products successfully."]);
    }

    public function syncCrm(string $id)
    {
        $product = DB::table('cms_products')->where('tenant_id', $this->tenantId())->where('id', $id)->first();
        abort_if(!$product, 404);
        $this->syncToCrm($id, (array) $product);
        return response()->json(['synced' => true]);
    }

    protected function syncToCrm(int $cmsProductId, array $data): void
    {
        try {
            $existing = DB::table('crm_products')
                ->where('tenant_id', $data['tenant_id'])
                ->where('cms_product_id', $cmsProductId)
                ->first();

            $crmData = [
                'name'            => $data['name'],
                'sku'             => $data['sku'] ?? null,
                'price'           => $data['price'],
                'description'     => $data['description'] ?? null,
                'is_active'       => $data['is_active'] ?? true,
                'cms_product_id'  => $cmsProductId,
                'updated_at'      => now(),
            ];

            if ($existing) {
                DB::table('crm_products')->where('id', $existing->id)->update($crmData);
            } else {
                DB::table('crm_products')->insert(array_merge($crmData, [
                    'tenant_id'  => $data['tenant_id'],
                    'created_at' => now(),
                ]));
            }
        } catch (\Exception $e) {
            // Fail silently — CRM sync is non-blocking
            \Log::warning('CMS→CRM product sync failed: ' . $e->getMessage());
        }
    }
}
