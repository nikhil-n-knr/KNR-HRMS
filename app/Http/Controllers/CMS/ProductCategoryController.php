<?php

namespace App\Http\Controllers\CMS;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductCategoryController extends Controller
{
    protected function tenantId(): int { return auth()->user()->tenant_id; }

    public function index(Request $request)
    {
        $siteId = $request->get('site_id') ?? DB::table('cms_sites')->where('tenant_id', $this->tenantId())->value('id');
        return response()->json(
            DB::table('cms_product_categories')
                ->where('tenant_id', $this->tenantId())
                ->when($siteId, fn($q) => $q->where('site_id', $siteId))
                ->orderBy('order')
                ->get()
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'slug'        => 'nullable|string',
            'parent_id'   => 'nullable|integer',
            'image'       => 'nullable|string',
            'description' => 'nullable|string',
            'order'       => 'nullable|integer',
        ]);
        $data['tenant_id']  = $this->tenantId();
        $data['site_id']    = $request->get('site_id') ?? DB::table('cms_sites')->where('tenant_id', $this->tenantId())->value('id');
        $data['slug']     ??= \Illuminate\Support\Str::slug($data['name']);
        $data['is_active']  = true;
        $data['created_at'] = $data['updated_at'] = now();
        $id = DB::table('cms_product_categories')->insertGetId($data);
        return response()->json(DB::table('cms_product_categories')->find($id), 201);
    }

    public function show(string $id)
    {
        return response()->json(DB::table('cms_product_categories')->where('tenant_id', $this->tenantId())->where('id', $id)->first());
    }

    public function update(Request $request, string $id)
    {
        $data = array_merge($request->only(['name','slug','parent_id','image','description','order','is_active']), ['updated_at' => now()]);
        DB::table('cms_product_categories')->where('tenant_id', $this->tenantId())->where('id', $id)->update($data);
        return response()->json(DB::table('cms_product_categories')->find($id));
    }

    public function destroy(string $id)
    {
        DB::table('cms_product_categories')->where('tenant_id', $this->tenantId())->where('id', $id)->delete();
        return response()->json(['deleted' => true]);
    }
}
