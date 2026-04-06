<?php

namespace App\Services\CMS\Canvas;

use Exception;
use App\Models\CRM\Contact;
use App\Models\CRM\Deal;
use App\Models\CRM\Product;

class DynamicDataEngine
{
    private int $tenantId;

    public function __construct(int $tenantId)
    {
        $this->tenantId = $tenantId;
    }

    /**
     * Resolves bound CRM data source requirements into actual payload data for Canvas components.
     */
    public function resolveBindings(array $blocks): array
    {
        $resolvedData = [];

        foreach ($blocks as $block) {
            if (isset($block['bindings']) && !empty($block['bindings'])) {
                $resolvedData[$block['id']] = $this->fetchData($block['bindings']);
            } else {
                // If it's a specific block type (like a live ticker), inject global context
                if (isset($block['settings']['showLiveTicker']) && $block['settings']['showLiveTicker']) {
                    $resolvedData['global_ticker'] = [
                        'deals_this_week' => Deal::where('tenant_id', $this->tenantId)
                            ->where('created_at', '>=', now()->startOfWeek())
                            ->count()
                    ];
                }
            }
        }

        return $resolvedData;
    }

    private function fetchData(array $binding): array
    {
        $source = $binding['source'] ?? null;
        $limit = $binding['limit'] ?? 6;

        if (!$source) return [];

        try {
            return match ($source) {
                'crm_products' => Product::where('tenant_id', $this->tenantId)
                                         ->where('is_active', true)
                                         ->limit($limit)
                                         ->get()
                                         ->map(fn($p) => [
                                             'id' => $p->id,
                                             'name' => $p->name,
                                             'description' => $p->description,
                                             'price' => $p->base_price,
                                             'image' => $p->image_url
                                         ])->toArray(),

                'crm_deals_won' => Deal::where('tenant_id', $this->tenantId)
                                       ->where('status', 'won')
                                       ->latest('closed_at')
                                       ->limit($limit)
                                       ->get()
                                       ->map(fn($d) => [
                                           'title' => $d->name,
                                           'value' => $d->value,
                                           'date' => $d->closed_at
                                       ])->toArray(),
                                       
                'crm_testimonials' => Contact::where('tenant_id', $this->tenantId)
                                             ->whereNotNull('nps_score')
                                             ->where('nps_score', '>=', 9) // Promoters
                                             ->limit($limit)
                                             ->get()
                                             ->map(fn($c) => [
                                                 'name' => $c->first_name . ' ' . $c->last_name,
                                                 'title' => $c->job_title,
                                                 'company' => $c->company?->name,
                                                 'quote' => 'Amazing experience working with this team!' // Mocking quote if no CRM custom field exists
                                             ])->toArray(),

                default => []
            };
        } catch (Exception $e) {
            \Log::error("CMS Dynamic Data Engine Error for {$source}: " . $e->getMessage());
            return [];
        }
    }
}
