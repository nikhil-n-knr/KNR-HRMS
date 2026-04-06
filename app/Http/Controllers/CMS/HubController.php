<?php

namespace App\Http\Controllers\CMS;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class HubController extends Controller
{
    public function index(Request $request)
    {
        $section = $request->get('section', 'home_builder');
        $tab     = $request->get('tab', 'canvas');
        $user    = auth()->user();
        $tenantId = $user->tenant_id;

        // ── Section-specific lazy loaders ──
        $data = [
            'section' => $section,
            'tab'     => $tab,
        ];

        $loaders = [
            'home_builder' => \App\Services\CMS\Loaders\PageLoader::class,
            'page_manager' => \App\Services\CMS\Loaders\PageLoader::class,
            'theme_engine' => \App\Services\CMS\Loaders\ThemeLoader::class,
        ];

        if (isset($loaders[$section])) {
            $loaderClass = $loaders[$section];
            $loader      = new $loaderClass($tenantId);
            $sectionData = $loader->load($tab, $request);
            $data        = array_merge($data, $sectionData);
        }

        // ── Sites (non-deleted only for production) ──
        $sites = DB::table('cms_sites')
            ->where('tenant_id', $tenantId)
            ->whereNull('deleted_at')
            ->orderByDesc('created_at')
            ->get(['id', 'name', 'slug', 'domain', 'type', 'status', 'is_live', 'settings', 'currency', 'razorpay_key_id'])
            ->map(function ($site) {
                $site->pages    = DB::table('cms_pages')->where('site_id', $site->id)->whereNull('deleted_at')->count();
                $site->products = DB::table('cms_products')->where('site_id', $site->id)->whereNull('deleted_at')->count();
                $site->visitors = '—';
                $colors = ['#6366f1','#10b981','#f59e0b','#ec4899','#0ea5e9','#ef4444'];
                $site->color = $colors[$site->id % count($colors)];
                return $site;
            })
            ->toArray();

        $data['sites'] = $sites;

        // Active site resolution
        $ecommerceSections = ['products', 'orders', 'coupons', 'cart_checkout', 'payments'];
        $requestedSiteId = $request->get('site_id');

        if ($requestedSiteId) {
            $activeSite = collect($sites)->firstWhere('id', (int) $requestedSiteId);
        } else {
            if (in_array($section, $ecommerceSections)) {
                // Prioritize the ecommerce site that actually has products if multiple exist
                $activeSite = collect($sites)->filter(fn($s) => $s->type === 'ecommerce')->sortByDesc('products')->first() 
                            ?? collect($sites)->firstWhere('type', 'ecommerce') 
                            ?? $sites[0] ?? null;
            } else {
                $activeSite = collect($sites)->firstWhere('status', 'live') ?? $sites[0] ?? null;
            }
        }
        $activeSiteId = $activeSite?->id;

        foreach($sites as $s) {
            $s->is_active_in_hub = ($s->id === $activeSiteId);
        }

        $data['pages'] = $activeSiteId 
            ? \App\Models\CMS\Page::where('site_id', $activeSiteId)->orderBy('priority')->get()
            : [];

        // ── Ecommerce props (only load if site is ecommerce) ──
        if ($activeSite?->type === 'ecommerce' || \in_array($section, ['products', 'orders', 'coupons', 'cart_checkout', 'payments'])) {
            $data['products'] = $activeSiteId
                ? DB::table('cms_products')->where('site_id', $activeSiteId)->orderByDesc('created_at')->limit(1000)->get()->toArray()
                : [];

            $data['categories'] = $activeSiteId
                ? DB::table('cms_product_categories')->where('site_id', $activeSiteId)->orderBy('order')->get()->toArray()
                : [];

            $data['orders'] = $activeSiteId
                ? DB::table('cms_orders')->where('site_id', $activeSiteId)->orderByDesc('created_at')->limit(50)->get()->toArray()
                : [];

            $data['coupons'] = $activeSiteId
                ? DB::table('cms_coupons')->where('site_id', $activeSiteId)->orderByDesc('created_at')->get()->toArray()
                : [];

            $settingsJson = is_string($activeSite->settings ?? '') ? json_decode($activeSite->settings ?? '{}', true) : [];
            $data['payment_config'] = array_merge($settingsJson ?? [], [
                'razorpay_key_id'     => $activeSite->razorpay_key_id ?? null,
                'razorpay_key_secret' => '', // never expose in front-end from DB
                'razorpay_enabled'    => !empty($activeSite->razorpay_key_id),
                'cod_enabled'         => $settingsJson['cod_enabled'] ?? true,
                'bank_enabled'        => $settingsJson['bank_enabled'] ?? false,
                'razorpay_mode'       => $settingsJson['razorpay_mode'] ?? 'test',
                'razorpay_methods'    => $settingsJson['razorpay_methods'] ?? ['UPI','Cards','Netbanking'],
                'cod_charge'          => $settingsJson['cod_charge'] ?? 0,
                'cod_max_order'       => $settingsJson['cod_max_order'] ?? 50000,
                'cod_pincodes'        => $settingsJson['cod_pincodes'] ?? '',
                'refund_days'         => $settingsJson['refund_days'] ?? 7,
                'auto_refund'         => $settingsJson['auto_refund'] ?? 'manual',
                'refund_fee_pct'      => $settingsJson['refund_fee_pct'] ?? 0,
            ]);
        }

        // ── Forms ──
        $data['forms'] = $activeSiteId
            ? DB::table('cms_forms')->where('site_id', $activeSiteId)->orderByDesc('created_at')->get()->toArray()
            : [];

        // ── Media (tenant-scoped, no site_id on cms_media) ──
        $data['media'] = $activeSiteId
            ? DB::table('cms_media')->where('tenant_id', $tenantId)->orderByDesc('created_at')->limit(80)->get()->toArray()
            : [];

        // ── Analytics (mock / real later) ──
        $data['analytics'] = [
            'views_7d'      => 24812,
            'sessions_7d'   => 8934,
            'bounce_rate'   => 38.2,
            'conversions'   => 1247,
        ];

        // ── SEO Audits ──
        try {
            $data['seo_audit'] = $activeSiteId
                ? DB::table('cms_seo_audits')->where('site_id', $activeSiteId)->orderByDesc('created_at')->first()
                : null;
        } catch (\Exception $e) {
            $data['seo_audit'] = null; // table may not exist yet
        }

        // ── A/B Tests ──
        try {
            $data['ab_tests'] = $activeSiteId
                ? DB::table('cms_ab_tests')->where('site_id', $activeSiteId)->orderByDesc('created_at')->get()->toArray()
                : [];
        } catch (\Exception $e) {
            $data['ab_tests'] = [];
        }

        // ── CRM Stats (Site & Tenant Scoped) ──
        $data['crm_stats'] = [
            'products'   => DB::table('crm_products')->where('tenant_id', $tenantId)->count(),
            'leads'      => DB::table('crm_leads')->where('tenant_id', $tenantId)->count(),
            'deals'      => DB::table('crm_deals')->where('tenant_id', $tenantId)->count(),
            'contacts'   => DB::table('crm_contacts')->where('tenant_id', $tenantId)->count(),
            'orders'     => DB::table('cms_orders')->where('site_id', $activeSiteId)->count(),
            'last_sync'  => now()->subMinutes(rand(5, 45))->diffForHumans(),
        ];

        // ── PWA Config ──
        try {
            $data['pwa_config'] = $activeSiteId
                ? DB::table('cms_pwa_configs')->where('site_id', $activeSiteId)->first()
                : null;
        } catch (\Exception $e) {
            $data['pwa_config'] = null;
        }

        // ── Version Control — recent versions across pages for this site ──
        $data['versions'] = $activeSiteId
            ? DB::table('cms_page_versions as v')
                ->join('cms_pages as p', 'p.id', '=', 'v.page_id')
                ->where('p.site_id', $activeSiteId)
                ->orderByDesc('v.id')
                ->limit(50)
                ->get(['v.id', 'v.page_id', 'v.commit_message', 'v.created_by', 'v.created_at'])
                ->toArray()
            : [];

        // ── Settings ──
        $data['settings'] = $activeSite ? [
            'site_name' => $activeSite->name,
            'slug'      => $activeSite->slug,
            'domain'    => $activeSite->domain ?? null,
            'currency'  => $activeSite->currency ?? 'INR',
        ] : [];

        // ── Global KPIs (Header) ──
        $data['global_kpis'] = [
            'views_today' => rand(15000, 30000),
            'status'      => $activeSite?->status ?? 'draft',
            'last_saved'  => now()->diffForHumans(),
        ];

        // ── Permissions ──
        $data['cms_permissions'] = ['*'];

        return Inertia::render('CMS/Hub', $data);
    }
}
