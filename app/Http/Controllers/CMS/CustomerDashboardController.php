<?php

namespace App\Http\Controllers\CMS;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class CustomerDashboardController extends PublicSiteController
{
    public function dashboard(Request $request)
    {
        $site = $this->resolveSite($request);
        return $this->renderArea($site, $request, 'dashboard');
    }

    public function orders(Request $request)
    {
        $site = $this->resolveSite($request);
        $customer = Auth::guard('customer')->user();
        
        $orders = DB::table('cms_orders')
            ->where('site_id', $site->id)
            ->where('customer_id', $customer->id)
            ->orderBy('created_at', 'desc')
            ->get();
            
        return $this->renderArea($site, $request, 'orders', ['orderList' => $orders]);
    }

    public function wishlist(Request $request)
    {
        $site = $this->resolveSite($request);
        $customer = Auth::guard('customer')->user();
        
        $wishlistIds = DB::table('cms_wishlists')
            ->where('site_id', $site->id)
            ->where('user_id', $customer->id)
            ->pluck('product_id')->toArray();
            
        $products = [];
        if (!empty($wishlistIds)) {
            $products = DB::table('cms_products')
                ->whereIn('id', $wishlistIds)
                ->get();
        }
        
        return $this->renderArea($site, $request, 'wishlist', ['wishlistProducts' => $products]);
    }

    public function toggleWishlist(Request $request, $productId)
    {
        $site = $this->resolveSite($request);
        $customer = Auth::guard('customer')->user();
        
        $exists = DB::table('cms_wishlists')
            ->where('site_id', $site->id)
            ->where('user_id', $customer->id)
            ->where('product_id', $productId)
            ->first();
            
        if ($exists) {
            DB::table('cms_wishlists')->where('id', $exists->id)->delete();
            return back()->with('success', 'Removed from wishlist');
        } else {
            DB::table('cms_wishlists')->insert([
                'user_id' => $customer->id,
                'product_id' => $productId,
                'site_id' => $site->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            return back()->with('success', 'Added to wishlist');
        }
    }

    protected function renderArea($site, $request, $viewType, $extraData = [])
    {
        $theme = $site->theme_id ? DB::table('cms_themes')->where('id', $site->theme_id)->first() : null;
        $themeConfig = $theme ? json_decode($theme->config ?? '{}', true) : [];
        
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

        $ecomData = $this->getEcomData($site, $categories);
        $forms = $this->getForms($site);
        
        // Fetch User's wishlist IDs so the frontend knows what is hearted
        $customer = Auth::guard('customer')->user();
        $wishlistIds = [];
        if ($customer) {
            $wishlistIds = DB::table('cms_wishlists')
                ->where('site_id', $site->id)
                ->where('user_id', $customer->id)
                ->pluck('product_id')->toArray();
        }

        $props = [
            'view_type' => 'account_' . $viewType,
            'site' => $this->mapSite($site),
            'seo' => ['title' => ucfirst($viewType) . ' - ' . $site->name],
            'nav_pages' => $navPages,
            'theme' => $themeConfig,
            'ecom' => $ecomData,
            'forms' => $forms,
            'wishlist_ids' => $wishlistIds,
        ];
        
        $props = array_merge($props, $extraData);

        return Inertia::render('CMS/Public/SitePage', $props);
    }
}
