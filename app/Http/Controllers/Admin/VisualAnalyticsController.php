<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\Location;

class VisualAnalyticsController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Analytics/VisualHub', [
            'sankey_data' => $this->getSankeyData(),
            'sunburst_data' => $this->getSunburstData()
        ]);
    }

    private function getSankeyData()
    {
        // Flow: Source (Fund/Vendor) -> Category -> Location (or Dept)
        
        $nodes = [];
        $links = [];
        $nodeMap = []; // Key => Index

        // Helper to add node
        $addNode = function($name) use (&$nodes, &$nodeMap) {
            if (!isset($nodeMap[$name])) {
                $nodeMap[$name] = count($nodes);
                $nodes[] = ['name' => $name];
            }
            return $nodeMap[$name];
        };

        // Source Node
        $rootParams = $addNode('Capital Expenditure');

        // 1. CapEx -> Categories
        $categories = Asset::selectRaw('category_id, SUM(purchase_cost) as total')
            ->groupBy('category_id')
            ->with('category')
            ->whereNotNull('purchase_cost')
            ->get();

        foreach ($categories as $cat) {
            $catName = $cat->category->name ?? 'Uncategorized';
            $catIndex = $addNode($catName);
            
            $links[] = [
                'source' => $rootParams,
                'target' => $catIndex,
                'value' => (float) $cat->total
            ];

            // 2. Category -> Locations (Simulating distribution)
            // We need to query again or structured query. 
            // For MVP, lets just query distribution for this category
            $distribution = Asset::where('category_id', $cat->category_id)
                ->join('locations', 'assets.location_id', '=', 'locations.id')
                ->selectRaw('locations.name as loc_name, SUM(assets.purchase_cost) as total')
                ->groupBy('locations.name')
                ->get();

            foreach ($distribution as $dist) {
                $locIndex = $addNode($dist->loc_name);
                 $links[] = [
                    'source' => $catIndex,
                    'target' => $locIndex,
                    'value' => (float) $dist->total
                ];
            }
        }

        return ['nodes' => $nodes, 'links' => $links];
    }

    private function getSunburstData()
    {
        // Hierarchy: Root -> Location -> Category -> Value
        $root = [
            'name' => 'Total Assets',
            'children' => []
        ];

        $locations = Location::with(['assets.category'])->get();

        foreach ($locations as $loc) {
            $locNode = [
                'name' => $loc->name,
                'children' => []
            ];

            // Group assets by category within location
            $grouped = $loc->assets->groupBy('category.name');

            foreach ($grouped as $catName => $assets) {
                $val = $assets->sum('purchase_cost');
                if ($val > 0) {
                    $locNode['children'][] = [
                        'name' => $catName,
                        'value' => $val
                    ];
                }
            }

            if (!empty($locNode['children'])) {
                $root['children'][] = $locNode;
            }
        }

        return $root;
    }
}
