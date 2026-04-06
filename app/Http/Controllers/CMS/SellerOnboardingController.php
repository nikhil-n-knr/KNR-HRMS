<?php

namespace App\Http\Controllers\CMS;

use App\Http\Controllers\Controller;
use App\Models\CMS\SellerApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class SellerOnboardingController extends Controller
{
    public function showForm(Request $request)
    {
        $site = $this->resolveSite($request);
        return Inertia::render('CMS/Public/BecomeSeller', [
            'site' => $site,
            'theme' => $this->getTheme($site),
        ]);
    }

    public function submit(Request $request)
    {
        $site = $this->resolveSite($request);

        $request->validate([
            'name' => 'required|string|max:255',
            'store_name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'required|string|max:20',
            'business_details' => 'nullable|string',
        ]);

        SellerApplication::create([
            'tenant_id' => $site->tenant_id,
            'name' => $request->name,
            'store_name' => $request->store_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'business_details' => $request->business_details,
            'status' => 'pending',
        ]);

        return back()->with('message', 'Application submitted! Our team will contact you soon.');
    }

    protected function resolveSite(Request $request)
    {
        if ($slug = $request->query('preview_site')) {
            $site = DB::table('cms_sites')->where('slug', $slug)->first();
            if ($site) return $site;
        }
        $site = DB::table('cms_sites')->where('domain', $request->getHost())->where('status', 'live')->first();
        if (!$site) $site = DB::table('cms_sites')->where('status', 'live')->first();
        if (!$site) $site = DB::table('cms_sites')->first();
        return $site;
    }

    protected function getTheme($site)
    {
        $theme = $site && $site->theme_id ? DB::table('cms_themes')->where('id', $site->theme_id)->first() : null;
        return $theme ? json_decode($theme->config ?? '{}', true) : [
            'colors' => ['primary' => '#4f46e5', 'secondary' => '#6366f1'],
            'typography' => ['font_heading' => 'Inter', 'font_body' => 'Inter'],
        ];
    }
}
