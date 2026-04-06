<?php

namespace App\Http\Controllers\CMS;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class PublicSiteController extends Controller
{
    /**
     * Resolve which site to serve based on the request host.
     * Priority: custom domain → slug match → first site with is_live
     */
    protected function resolveSite(Request $request): ?object
    {
        // 0. Manual preview override
        if ($slug = $request->query('preview_site')) {
            $site = DB::table('cms_sites')->where('slug', $slug)->first();
            if ($site) return $site;
        }

        $host = $request->getHost();

        // 1. Domain match
        $site = DB::table('cms_sites')->where('domain', $host)->where('status', 'live')->first();

        // 2. Subdomain check
        if (!$site && str_contains($host, '.')) {
            $subdomain = explode('.', $host)[0];
            $site = DB::table('cms_sites')->where('slug', $subdomain)->where('status', 'live')->first();
        }

        // 3. Fallback: First published site (Crucial for localhost:8000)
        if (!$site) {
            $site = DB::table('cms_sites')->where('status', 'live')->first();
        }

        // 4. Emergency Fallback: Any site
        if (!$site) {
            $site = DB::table('cms_sites')->first();
        }

        return $site;
    }

    public function serve(Request $request, string $slug = '/')
    {
        $site = $this->resolveSite($request);

        if (!$site) {
            // Instead of redirecting to login which confuses users, show a placeholder or let Laravel 404
            if (auth()->check()) return redirect()->route('dashboard');
            return Inertia::render('Auth/Login'); // Still fallback to login if literally no CMS sites exist
        }

        $cleanSlug = ltrim($slug, '/');
        $fullPath  = $cleanSlug === '' ? '/' : '/' . $cleanSlug;

        // Global Data
        $theme = $site->theme_id ? DB::table('cms_themes')->where('id', $site->theme_id)->first() : null;
        $themeConfig = $theme ? json_decode($theme->config ?? '{}', true) : [
            'colors' => ['primary' => '#10b981', 'secondary' => '#6366f1'],
            'typography' => ['font_heading' => 'Inter', 'font_body' => 'Inter'],
        ];

        $navPages = DB::table('cms_pages')
            ->where('site_id', $site->id)
            ->where('status', 'published')
            ->orderBy('priority')
            ->get(['id', 'title', 'slug', 'is_home'])
            ->toArray();

        $categories = DB::table('cms_product_categories')
            ->where('site_id', $site->id)
            ->where('is_active', true)
            ->orderBy('order')
            ->get();

        // 0. System Routes: Search
        if ($cleanSlug === 'search') {
            $q = $request->query('q', '');
            $products = DB::table('cms_products')
                ->where('site_id', $site->id)
                ->where('is_active', true)
                ->whereNull('deleted_at')
                ->where(function ($query) use ($q) {
                    $query->where('name', 'like', '%' . $q . '%')
                          ->orWhere('description', 'like', '%' . $q . '%')
                          ->orWhere('sku', 'like', '%' . $q . '%');
                })
                ->orderBy('sort_order')
                ->paginate(24);
                
            return Inertia::render('CMS/Public/SitePage', [
                'view_type' => 'search',
                'site'      => $this->mapSite($site),
                'products'  => $products,
                'seo'       => ['title' => 'Search Results: ' . $q],
                'nav_pages' => $navPages,
                'theme'     => $themeConfig,
                'ecom'      => $this->getEcomData($site, $categories),
                'search_query' => $q
            ]);
        }

        // 1. Try CMS Page
        $page = DB::table('cms_pages')
            ->where('site_id', $site->id)
            ->where('slug', $fullPath)
            ->where('status', 'published')
            ->first();

        // If it's root and no "/" slug page, try is_home
        if (!$page && $fullPath === '/') {
            $page = DB::table('cms_pages')
                ->where('site_id', $site->id)
                ->where('is_home', true)
                ->where('status', 'published')
                ->first();
        }

        if ($page) {
            return $this->renderPage($site, $page, $request, $themeConfig, $navPages, $categories);
        }

        // 2. Try e-commerce routes
        if ($site->type === 'ecommerce') {
            // Category
            $cat = $categories->where('slug', $cleanSlug)->first();
            if ($cat) return $this->renderCategory($site, $cat, $request, $themeConfig, $navPages, $categories);

            // Product
            $prod = DB::table('cms_products')
                ->where('site_id', $site->id)
                ->where('slug', $cleanSlug)
                ->where('is_active', true)
                ->whereNull('deleted_at')
                ->first();
            if ($prod) return $this->renderProduct($site, $prod, $request, $themeConfig, $navPages, $categories);
        }

        return Inertia::render('CMS/Public/SitePage', [
            'view_type' => 'error',
            'site'      => $this->mapSite($site),
            'seo'       => ['title' => 'Not Found'],
            'nav_pages' => $navPages,
            'ecom'      => $this->getEcomData($site, $categories),
            'theme'     => $themeConfig
        ]);
    }

    protected function renderPage($site, $page, $request, $themeConfig, $navPages, $categories)
    {
        $layoutData = json_decode($page->layout_data ?? '{}', true);
        $blocks     = $layoutData['blocks'] ?? [];
        $seoMeta    = json_decode($page->seo_meta ?? '{}', true);

        $seo = [
            'title'       => $seoMeta['title']       ?? ($page->title . ' – ' . $site->name),
            'description' => $seoMeta['description'] ?? '',
            'og_image'    => $seoMeta['og_image']    ?? null,
            'keywords'    => $seoMeta['keywords']    ?? '',
            'canonical'   => ($site->domain ? 'https://' . $site->domain : '') . $page->slug,
            'schema'      => $seoMeta['schema']      ?? null,
        ];

        $ecomData = $this->getEcomData($site, $categories);
        $forms    = $this->getForms($site);
        $pwa      = DB::table('cms_pwa_configs')->where('site_id', $site->id)->first();

        $this->trackView($request, $site->id, $page->id);

        return Inertia::render('CMS/Public/SitePage', [
            'view_type' => 'page',
            'site'      => $this->mapSite($site),
            'page'      => $page,
            'blocks'    => $blocks,
            'seo'       => $seo,
            'nav_pages' => $navPages,
            'theme'     => $themeConfig,
            'ecom'      => $ecomData,
            'pwa'       => $pwa,
            'forms'     => $forms,
        ]);
    }

    protected function renderCategory($site, $cat, $request, $themeConfig, $navPages, $categories)
    {
        $products = DB::table('cms_products')
            ->where('site_id', $site->id)
            ->where(function($q) use ($cat, $categories) {
                 $childIds = $categories->where('parent_id', $cat->id)->pluck('id')->toArray();
                 $q->where('category_id', $cat->id)->orWhere('sub_category_id', $cat->id);
                 if (!empty($childIds)) {
                     $q->orWhereIn('category_id', $childIds)->orWhereIn('sub_category_id', $childIds);
                 }
            })
            ->where('is_active', true)
            ->whereNull('deleted_at')
            ->orderBy('sort_order')
            ->paginate(24);

        $seo = [
            'title'       => $cat->name . ' – ' . $site->name,
            'description' => $cat->description ?? "Shop {$cat->name} online.",
            'canonical'   => ($site->domain ? 'https://' . $site->domain : '') . '/' . $cat->slug,
        ];

        $ecomData = $this->getEcomData($site, $categories);
        $forms    = $this->getForms($site);
        $pwa      = DB::table('cms_pwa_configs')->where('site_id', $site->id)->first();
        $this->trackView($request, $site->id, null);

        return Inertia::render('CMS/Public/SitePage', [
            'view_type' => 'category',
            'site'      => $this->mapSite($site),
            'category'  => $cat,
            'products'  => $products,
            'seo'       => $seo,
            'nav_pages' => $navPages,
            'theme'     => $themeConfig,
            'ecom'      => $ecomData,
            'pwa'       => $pwa,
            'forms'     => $forms,
        ]);
    }

    protected function renderProduct($site, $prod, $request, $themeConfig, $navPages, $categories)
    {
        $related = DB::table('cms_products')
            ->where('site_id', $site->id)
            ->where('category_id', $prod->category_id)
            ->where('id', '!=', $prod->id)
            ->limit(4)
            ->get();

        $seo = [
            'title'       => $prod->name . ' – ' . $site->name,
            'description' => $prod->short_description ?? substr(strip_tags($prod->description), 0, 160),
            'og_image'    => json_decode($prod->images, true)[0] ?? null,
            'canonical'   => ($site->domain ? 'https://' . $site->domain : '') . '/' . $prod->slug,
        ];

        $ecomData = $this->getEcomData($site, $categories);
        $forms    = $this->getForms($site);
        $pwa      = DB::table('cms_pwa_configs')->where('site_id', $site->id)->first();
        $this->trackView($request, $site->id, null);

        return Inertia::render('CMS/Public/SitePage', [
            'view_type' => 'product',
            'site'      => $this->mapSite($site),
            'product'   => $prod,
            'related'   => $related,
            'seo'       => $seo,
            'nav_pages' => $navPages,
            'theme'     => $themeConfig,
            'ecom'      => $ecomData,
            'pwa'       => $pwa,
            'forms'     => $forms,
        ]);
    }

    protected function getEcomData($site, $categories)
    {
        if ($site->type !== 'ecommerce') return null;
        return [
            'categories' => $categories,
            'razorpay_key_id' => $site->razorpay_key_id,
            'currency'        => $site->currency ?? 'INR',
            'featured'        => DB::table('cms_products')
                ->where('site_id', $site->id)
                ->where('featured', true)
                ->limit(20)->get()->toArray(),
            'homepage_products' => DB::table('cms_products')
                ->where('site_id', $site->id)
                ->where('is_active', true)
                ->limit(60)->get()->toArray(),
        ];
    }

    protected function getForms($site)
    {
        return DB::table('cms_forms')
            ->where('site_id', $site->id)
            ->where('is_active', true)
            ->get(['id', 'name', 'slug', 'fields'])
            ->toArray();
    }

    protected function mapSite($site)
    {
        $customer = null;
        try {
            $customer = Auth::guard('customer')->user();
        } catch (\Exception $e) {
            // Guard not defined or other auth error
        }
        
        return [
            'id'       => $site->id,
            'name'     => $site->name,
            'domain'   => $site->domain,
            'type'     => $site->type,
            'currency' => $site->currency ?? 'INR',
            'settings' => json_decode($site->settings ?? '{}', true),
            'user'     => $customer ? [
                'name'  => $customer->name,
                'email' => $customer->email,
            ] : null,
        ];
    }

    /**
     * Serve the PWA manifest.json dynamically.
     */
    public function manifest(Request $request)
    {
        $site   = $this->resolveSite($request);
        $config = $site
            ? DB::table('cms_pwa_configs')->where('site_id', $site->id)->first()
            : null;

        $manifest = [
            'name'             => $config?->app_name ?? ($site?->name ?? 'My Site'),
            'short_name'       => $config?->short_name ?? substr($site?->name ?? 'Site', 0, 12),
            'description'      => $config?->description ?? '',
            'start_url'        => '/',
            'display'          => $config?->display ?? 'standalone',
            'theme_color'      => $config?->theme_color ?? '#10b981',
            'background_color' => $config?->background_color ?? '#ffffff',
            'icons'            => [
                ['src' => '/icon-192.png', 'sizes' => '192x192', 'type' => 'image/png'],
                ['src' => '/icon-512.png', 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any maskable'],
            ],
            'categories'   => ['business'],
            'lang'         => 'en-IN',
        ];

        return response()->json($manifest, 200, [
            'Content-Type' => 'application/manifest+json',
        ]);
    }

    /**
     * Serve the sitemap.xml.
     */
    public function sitemap(Request $request)
    {
        $site  = $this->resolveSite($request);
        $pages = $site
            ? DB::table('cms_pages')->where('site_id', $site->id)->where('status', 'published')->get()
            : collect();

        $domain = $site?->domain ? 'https://' . $site->domain : url('/');
        $xml    = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml   .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($pages as $page) {
            $url  = rtrim($domain, '/') . '/' . ltrim($page->slug, '/');
            $lastmod = date('Y-m-d', strtotime($page->updated_at));
            $xml .= "  <url><loc>{$url}</loc><lastmod>{$lastmod}</lastmod><changefreq>weekly</changefreq><priority>0.8</priority></url>\n";
        }
        $xml .= '</urlset>';

        return response($xml, 200, ['Content-Type' => 'application/xml']);
    }

    /**
     * Serve robots.txt.
     */
    public function robots(Request $request)
    {
        $site   = $this->resolveSite($request);
        $domain = $site?->domain ? 'https://' . $site->domain : url('/');

        $txt = "User-agent: *\nAllow: /\nDisallow: /cms/\nDisallow: /admin/\n\nSitemap: {$domain}/sitemap.xml\n";
        return response($txt, 200, ['Content-Type' => 'text/plain']);
    }

    /**
     * Non-blocking page view tracker.
     */
    protected function trackView(Request $request, int $siteId, ?int $pageId): void
    {
        try {
            $ua = strtolower($request->userAgent() ?? '');
            $device = str_contains($ua, 'mobile') ? 'mobile' :
                      (str_contains($ua, 'tablet') ? 'tablet' : 'desktop');

            DB::table('cms_page_views')->insert([
                'site_id'     => $siteId,
                'page_id'     => $pageId,
                'session_id'  => $request->cookie('psp_sid', \Str::random(32)),
                'url'         => $request->fullUrl(),
                'referrer'    => $request->header('Referer'),
                'device_type' => $device,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        } catch (\Exception $e) {
            // Non-blocking — analytics should never crash the page
        }
    }
}
