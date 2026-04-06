<?php

namespace App\Http\Controllers\CMS;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SettingsController extends Controller
{
    protected function tenantId(): int { return auth()->user()->tenant_id; }

    public function savePayment(Request $request) { return $this->updateEcommerce($request); }
    public function saveCart(Request $request) { return $this->updateEcommerce($request); }
    public function savePwa(Request $request) { return $this->updateEcommerce($request); }
    public function save(Request $request) { return $this->updateGeneral($request); }

    protected function updateEcommerce(Request $request)
    {
        $siteId = $request->get('site_id') ?? DB::table('cms_sites')
            ->where('tenant_id', $this->tenantId())
            ->where('type', 'ecommerce')
            ->value('id');

        if (!$siteId) {
             // Fallback to first site if no ecommerce type found
             $siteId = DB::table('cms_sites')->where('tenant_id', $this->tenantId())->value('id');
        }

        if (!$siteId) return response()->json(['error' => 'No site found'], 404);

        $site = DB::table('cms_sites')->where('id', $siteId)->first();
        $currentSettings = json_decode($site->settings ?? '{}', true);
        
        // Merge settings from request
        $newSettings = array_merge($currentSettings, $request->except(['site_id', '_token']));
        
        // Specific fields that might be elevated to actual columns if needed
        $updateData = ['settings' => json_encode($newSettings), 'updated_at' => now()];
        
        if ($request->has('razorpay_key_id')) {
            $updateData['razorpay_key_id'] = $request->razorpay_key_id;
        }
        if ($request->has('razorpay_key_secret')) {
             $updateData['razorpay_key_secret'] = $request->razorpay_key_secret;
        }

        DB::table('cms_sites')->where('id', $siteId)->update($updateData);

        return response()->json(['message' => 'Settings updated successfully']);
    }

    protected function updateGeneral(Request $request)
    {
        $siteId = $request->get('site_id') ?? DB::table('cms_sites')
            ->where('tenant_id', $this->tenantId())
            ->value('id');

        if (!$siteId) return response()->json(['error' => 'No site found'], 404);

        $data = $request->only(['name', 'slug', 'domain', 'currency']);
        $data['updated_at'] = now();

        DB::table('cms_sites')->where('id', $siteId)->update($data);

        return response()->json(['message' => 'General settings updated successfully']);
    }
}
