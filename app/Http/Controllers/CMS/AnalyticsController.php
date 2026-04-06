<?php

namespace App\Http\Controllers\CMS;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    protected function tenantId(): int { return auth()->user()->tenant_id; }

    /**
     * Overview stats for the Analytics Dashboard module.
     */
    public function overview(Request $request)
    {
        $siteId = $request->get('site_id') ?? DB::table('cms_sites')->where('tenant_id', $this->tenantId())->value('id');
        $days   = (int) $request->get('days', 7);

        $since = now()->subDays($days);

        $totalViews    = DB::table('cms_page_views')->where('site_id', $siteId)->where('created_at', '>=', $since)->count();
        $uniqueSessions = DB::table('cms_page_views')->where('site_id', $siteId)->where('created_at', '>=', $since)->distinct('session_id')->count('session_id');
        $mobileViews   = DB::table('cms_page_views')->where('site_id', $siteId)->where('device_type', 'mobile')->where('created_at', '>=', $since)->count();
        $desktopViews  = DB::table('cms_page_views')->where('site_id', $siteId)->where('device_type', 'desktop')->where('created_at', '>=', $since)->count();
        $tabletViews   = DB::table('cms_page_views')->where('site_id', $siteId)->where('device_type', 'tablet')->where('created_at', '>=', $since)->count();

        $topPages = DB::table('cms_page_views')
            ->where('site_id', $siteId)
            ->where('created_at', '>=', $since)
            ->select('url', DB::raw('COUNT(*) as views'))
            ->groupBy('url')
            ->orderByDesc('views')
            ->limit(10)
            ->get();

        // Daily views for chart
        $dailyViews = DB::table('cms_page_views')
            ->where('site_id', $siteId)
            ->where('created_at', '>=', $since)
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as views'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Ecom conversions (if active)
        $orders = DB::table('cms_orders')
            ->where('site_id', $siteId)
            ->where('payment_status', 'paid')
            ->where('created_at', '>=', $since)
            ->selectRaw('COUNT(*) as count, SUM(total) as revenue')
            ->first();

        return response()->json([
            'total_views'     => $totalViews,
            'unique_sessions' => $uniqueSessions,
            'devices'         => ['mobile' => $mobileViews, 'desktop' => $desktopViews, 'tablet' => $tabletViews],
            'top_pages'       => $topPages,
            'daily_views'     => $dailyViews,
            'conversions'     => ['orders' => $orders->count ?? 0, 'revenue' => $orders->revenue ?? 0],
        ]);
    }

    /**
     * Public page view tracker — called by frontend on every page load.
     * No auth required.
     */
    public function track(Request $request)
    {
        $data = $request->validate([
            'site_id'    => 'required|integer',
            'page_id'    => 'nullable|integer',
            'session_id' => 'required|string|max:64',
            'url'        => 'nullable|string',
            'referrer'   => 'nullable|string',
            'device_type'=> 'nullable|in:desktop,tablet,mobile',
        ]);

        // Detect device from User-Agent if not provided
        if (!isset($data['device_type'])) {
            $ua = strtolower($request->userAgent() ?? '');
            $data['device_type'] = str_contains($ua, 'mobile') ? 'mobile' : (str_contains($ua, 'tablet') ? 'tablet' : 'desktop');
        }

        DB::table('cms_page_views')->insert([
            'site_id'     => $data['site_id'],
            'page_id'     => $data['page_id'] ?? null,
            'session_id'  => $data['session_id'],
            'url'         => $data['url'] ?? null,
            'referrer'    => $data['referrer'] ?? null,
            'device_type' => $data['device_type'],
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        return response()->json(['tracked' => true]);
    }
}
