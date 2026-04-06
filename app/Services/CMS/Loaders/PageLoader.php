<?php

namespace App\Services\CMS\Loaders;

use Illuminate\Http\Request;
use App\Models\CMS\Page;

class PageLoader extends SectionLoader
{
    public function load(string $tab, Request $request): array
    {
        return match ($tab) {
            'canvas' => $this->loadCanvasEngine($request),
            'all_pages' => $this->loadAllPages($request),
            'history' => $this->loadHistory($request),
            default => $this->loadAllPages($request),
        };
    }

    private function loadAllPages(Request $request)
    {
        return [
            'pages' => Page::where('tenant_id', $this->tenantId)
                ->with('site')
                ->latest()
                ->get()
        ];
    }
    private function loadCanvasEngine(Request $request)
    {
        // Setup default block structure for a brand new page
        $blocks = [
            [
                'id' => 'hero_1',
                'type' => 'hero',
                'name' => 'Main Hero Cover',
                'height' => 600,
                'content' => [
                    'title' => 'Empower Your Teams with Connected Data',
                    'subtitle' => 'Deploy 100K pages per tenant natively integrated with your CRM. Powered by AI and KNR Office CMS Engine.'
                ],
                'settings' => [
                    'showLiveTicker' => true
                ]
            ],
            [
                'id' => 'feat_1',
                'type' => 'features',
                'name' => 'CRM Linked Database',
                'height' => 400,
                'content' => [
                    'title' => 'Live Connected CRM Products'
                ],
                'bindings' => [
                    'source' => 'crm_products',
                    'limit' => 3
                ]
            ]
        ];

        // Resolve any bound CRM payload
        $engine = new \App\Services\CMS\Canvas\DynamicDataEngine($this->tenantId);
        $dynamicData = $engine->resolveBindings($blocks);

        return [
            'blocks' => $blocks,
            'dynamicData' => $dynamicData
        ];
    }

    private function loadHistory(Request $request)
    {
        return [
            'versions' => []
        ];
    }
}
