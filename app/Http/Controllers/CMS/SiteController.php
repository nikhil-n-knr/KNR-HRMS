<?php

namespace App\Http\Controllers\CMS;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CMS\Site;
use Illuminate\Support\Str;

class SiteController extends Controller
{
    protected function tenantId(): int
    {
        return auth()->user()->tenant_id;
    }

    public function index()
    {
        return response()->json(
            Site::where('tenant_id', $this->tenantId())->orderByDesc('created_at')->get()
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'type'     => 'required|in:static,ecommerce',
            'domain'   => 'nullable|string|max:255',
            'currency' => 'nullable|string|max:3',
        ]);

        $data['tenant_id'] = $this->tenantId();
        $data['status']    = 'draft';
        $data['is_live']   = false;
        $data['slug']      = Str::slug($data['name']);
        $data['global_settings'] = [];
        $data['settings']  = [];
        $data['currency'] ??= 'INR';

        $site = Site::create($data);

        // Auto-create a default Home page for the site
        \App\Models\CMS\Page::create([
            'tenant_id'   => $data['tenant_id'],
            'site_id'     => $site->id,
            'title'       => 'Home',
            'slug'        => '/',
            'status'      => 'draft',
            'priority'    => 1,
            'layout_data' => ['blocks' => []],
            'seo_meta'    => ['title' => $data['name'] . ' – Home', 'description' => ''],
        ]);

        return response()->json($site, 201);
    }

    public function show(string $id)
    {
        $site = Site::where('tenant_id', $this->tenantId())->with(['pages', 'theme'])->findOrFail($id);
        return response()->json($site);
    }

    public function update(Request $request, string $id)
    {
        $site = Site::where('tenant_id', $this->tenantId())->findOrFail($id);
        $data = $request->validate([
            'name'   => 'nullable|string|max:255',
            'domain' => 'nullable|string|max:255',
            'type'   => 'nullable|in:static,ecommerce',
        ]);
        $site->update($data);
        return response()->json($site);
    }

    public function destroy(string $id)
    {
        Site::where('tenant_id', $this->tenantId())->findOrFail($id)->delete();
        return response()->json(['message' => 'Site deleted.']);
    }

    /**
     * Toggle site type between static and ecommerce.
     */
    public function toggleMode(Request $request, string $id)
    {
        $site = Site::where('tenant_id', $this->tenantId())->findOrFail($id);
        $newType = $site->type === 'static' ? 'ecommerce' : 'static';
        $site->update(['type' => $newType]);
        return response()->json(['type' => $newType]);
    }

    /**
     * Toggle live/draft status.
     */
    public function publish(string $id)
    {
        $site = Site::where('tenant_id', $this->tenantId())->findOrFail($id);
        $newStatus = $site->status === 'live' ? 'draft' : 'live';
        $site->update(['status' => $newStatus, 'is_live' => $newStatus === 'live']);
        return response()->json(['status' => $newStatus]);
    }

    /**
     * Save global site settings (payment keys, social, analytics).
     */
    public function saveSettings(Request $request, string $id)
    {
        $site = Site::where('tenant_id', $this->tenantId())->findOrFail($id);
        $validated = $request->validate([
            'razorpay_key_id'     => 'nullable|string',
            'razorpay_key_secret' => 'nullable|string',
            'currency'            => 'nullable|string|max:3',
            'settings'            => 'nullable|array',
        ]);

        $updateData = [];
        if (isset($validated['razorpay_key_id']))     $updateData['razorpay_key_id']     = $validated['razorpay_key_id'];
        if (isset($validated['razorpay_key_secret'])) $updateData['razorpay_key_secret'] = $validated['razorpay_key_secret'];
        if (isset($validated['currency']))             $updateData['currency']             = $validated['currency'];
        if (isset($validated['settings']))             $updateData['settings']             = $validated['settings'];

        $site->update($updateData);
        return response()->json(['saved' => true]);
    }
}
