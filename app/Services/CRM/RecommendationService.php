<?php

namespace App\Services\CRM;

use App\Models\CRM\Contact;
use App\Models\CRM\Product;
use App\Models\CRM\Deal;
use App\Models\CRM\QuoteItem;
use App\Models\CRM\ProductSalesHistory;
use Illuminate\Support\Collection;

class RecommendationService
{
    /**
     * Get product recommendations for a specific contact
     */
    public function getRecommendations(Contact $contact, $limit = 3): Collection
    {
        // 1. Get products the contact has already purchased or been quoted
        $purchasedProductIds = $this->getPurchasedProductIds($contact);

        // 2. Collaborative Filtering (Simple): "Customers who bought what you bought also bought..."
        $collaborativeRecs = $this->getCollaborativeRecommendations($purchasedProductIds, $limit);

        // 3. Trending Products (General popularity) if we don't have enough personalized data
        if ($collaborativeRecs->count() < $limit) {
            $trendingRecs = $this->getTrendingProducts($limit - $collaborativeRecs->count(), $purchasedProductIds);
            $collaborativeRecs = $collaborativeRecs->merge($trendingRecs);
        }

        return $collaborativeRecs;
    }

    /**
     * Get IDs of products purchased or quoted for this contact
     */
    private function getPurchasedProductIds(Contact $contact): array
    {
        // Get generic deals linked to contact
        // Ideally we would look at 'won' deals -> quotes -> items -> product_id
        // For now, let's assume we can traverse Deals -> Quotes -> Items
        
        return QuoteItem::whereHas('quote', function($q) use ($contact) {
                $q->where('contact_id', $contact->id)
                  ->whereIn('status', ['accepted', 'invoiced', 'paid']); // Only successful interactions
            })
            ->whereNotNull('product_id')
            ->pluck('product_id')
            ->toArray();
    }

    /**
     * Find products often bought together with the input products
     */
    private function getCollaborativeRecommendations(array $seedProductIds, $limit): Collection
    {
        if (empty($seedProductIds)) {
            return collect([]);
        }

        // Find other quotes that contain these products
        $relatedQuoteIds = QuoteItem::whereIn('product_id', $seedProductIds)
            ->pluck('quote_id');

        // Find OTHER products in those same quotes
        return Product::whereHas('quoteItems', function($q) use ($relatedQuoteIds, $seedProductIds) {
                $q->whereIn('quote_id', $relatedQuoteIds)
                  ->whereNotIn('product_id', $seedProductIds); // Exclude what they already bought
            })
            ->withCount(['quoteItems as popularity' => function($q) use ($relatedQuoteIds) {
                $q->whereIn('quote_id', $relatedQuoteIds);
            }])
            ->orderByDesc('popularity')
            ->take($limit)
            ->get();
    }

    /**
     * Get globally trending products based on sales velocity
     */
    private function getTrendingProducts($limit, array $excludeIds = []): Collection
    {
        return Product::whereNotIn('id', $excludeIds)
            ->where('is_active', true)
            ->orderByDesc('base_price') // Placeholder for "popularity" until SalesHistory is fully populated
            ->take($limit)
            ->get();
    }
}
