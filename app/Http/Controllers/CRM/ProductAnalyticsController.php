<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use App\Models\CRM\Product;
use App\Models\CRM\ProductAnalytics;
use App\Models\CRM\ProductSalesHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ProductAnalyticsController extends Controller
{
    public function index()
    {
        return Inertia::render('CRM/Sections/Analytics/ProductDashboard', [
            'top_selling' => $this->getMetric('top_selling_month'),
            'profit_heatmap' => $this->getMetric('profit_margin_heatmap'),
            'recurring_trends' => $this->getMetric('recurring_revenue_trend'),
            // Real-time fallback if cache empty
        ]);
    }

    private function getMetric($key)
    {
        $tenantId = auth()->user()->tenant_id;
        $cached = ProductAnalytics::where('tenant_id', $tenantId)
            ->where('metric_key', $key)
            ->where('calculated_at', '>', now()->subHours(24)) // 24h cache
            ->first();

        if ($cached) {
            return $cached->data;
        }

        // Calculate and cache if missing
        $data = $this->calculateMetric($key, $tenantId);
        
        ProductAnalytics::updateOrCreate(
            ['tenant_id' => $tenantId, 'metric_key' => $key],
            ['data' => $data, 'calculated_at' => now()]
        );

        return $data;
    }

    private function calculateMetric($key, $tenantId)
    {
        switch ($key) {
            case 'top_selling_month':
                return ProductSalesHistory::where('tenant_id', $tenantId)
                    ->whereMonth('period', now()->month)
                    ->with('product:id,name,sku')
                    ->orderByDesc('units_sold')
                    ->take(10)
                    ->get();
            
            case 'profit_margin_heatmap':
                // Return products grouped by category with margin
                return Product::where('tenant_id', $tenantId)
                    ->with('category:id,name')
                    ->select('id', 'name', 'category_id', 'base_price', 'cost_price')
                    ->get()
                    ->map(function ($p) {
                        return [
                            'name' => $p->name,
                            'category' => $p->category?->name ?? 'Uncategorized',
                            'margin' => $p->profit_margin
                        ];
                    });

            case 'recurring_revenue_trend':
                // Aggregate last 12 months recurring vs one-time
                return ProductSalesHistory::where('tenant_id', $tenantId)
                    ->selectRaw("DATE_FORMAT(period, '%Y-%m') as month, recurring_flag, SUM(revenue) as total")
                    ->groupBy('month', 'recurring_flag')
                    ->orderBy('month')
                    ->get()
                    ->groupBy('recurring_flag'); 
                    
            default:
                return [];
        }
    }

    public function refresh()
    {
        // Force refresh logic here
        return redirect()->back();
    }
}
