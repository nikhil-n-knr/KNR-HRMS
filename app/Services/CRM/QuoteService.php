<?php

namespace App\Services\CRM;

use App\Models\CRM\Quote;
use App\Models\CRM\Deal;
use Illuminate\Support\Str;

class QuoteService
{
    /**
     * Generate a unique quote number
     */
    public function generateQuoteNumber($tenantId)
    {
        $year = now()->year;
        $count = Quote::where('tenant_id', $tenantId)
            ->whereYear('created_at', $year)
            ->count() + 1;
            
        return "Q-{$year}-" . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Create a quote from a Deal
     */
    public function createFromDeal(Deal $deal, array $data)
    {
        $quote = Quote::create([
            'tenant_id' => $deal->tenant_id,
            'deal_id' => $deal->id,
            'account_id' => $deal->account_id,
            'contact_id' => $deal->contact_id,
            'created_by' => auth()->id(),
            'quote_number' => $this->generateQuoteNumber($deal->tenant_id),
            'title' => $data['title'] ?? "Quote for " . $deal->name,
            'valid_until' => now()->addDays(30),
            'status' => 'draft',
            'tax_rate' => $data['tax_rate'] ?? 0,
        ]);

        return $quote;
    }

    /**
     * Prepare a print-ready HTML preview
     */
    public function getPreviewHtml(Quote $quote)
    {
        $quote->load(['items', 'account', 'contact', 'template']);
        
        // This would typically use a Blade view, but for now we return a structured layout info
        return [
            'quote' => $quote,
            'items' => $quote->items,
            'header' => $quote->template->header_html ?? '<h1>QUOTE</h1>',
            'footer' => $quote->template->footer_html ?? '',
            'terms' => $quote->terms ?? $quote->template->terms_template ?? '',
        ];
    }
}
