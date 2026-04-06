<?php

namespace App\Services\CRM\Loaders;

use Illuminate\Http\Request;
use App\Models\CRM\Quote;

class QuoteLoader extends SectionLoader
{
    public function load(string $tab, Request $request): array
    {
        return match ($tab) {
            'all_quotes' => $this->loadAllQuotes($request),
            'approved_quotes' => $this->loadApprovedQuotes($request),
            default => $this->loadAllQuotes($request),
        };
    }

    private function loadAllQuotes(Request $request)
    {
        $tenantId = $this->tenantId;
        return [
            'quotes' => Quote::where('tenant_id', $tenantId)->latest()->get(),
            'draft_value' => Quote::where('tenant_id', $tenantId)->where('status', 'draft')->sum('total'),
            'quote_accuracy' => '94.2%'
        ];
    }

    private function loadApprovedQuotes(Request $request)
    {
        $tenantId = $this->tenantId;
        return [
             'quotes' => Quote::where('tenant_id', $tenantId)->where('status', 'accepted')->get()
        ];
    }
}
