<?php

namespace App\Services\CRM\Loaders;

use Illuminate\Http\Request;
use App\Models\CRM\Contact;
use App\Models\CRM\Account;
use App\Models\CRM\ContactSegment;
use App\Models\CRM\ContactImport;
use App\Models\CRM\Partner;

class ContactLoader extends SectionLoader
{
    public function load(string $tab, Request $request): array
    {
        return match ($tab) {
            'all_contacts' => $this->loadAllContacts($request),
            'companies' => $this->loadCompanies(),
            'segments' => $this->loadSegments(),
            'import_export' => $this->loadImports(),
            'duplicates' => $this->loadDuplicates(),
            'partners' => $this->loadPartners(),
            default => [],
        };
    }

    private function loadAllContacts(Request $request)
    {
        $data = [
            'contacts' => Contact::where('tenant_id', $this->tenantId)
                ->with(['account', 'creator'])
                ->latest()
                ->get(),
            'accounts' => Account::where('tenant_id', $this->tenantId)->get(['id', 'name']),
            'partners' => Partner::where('tenant_id', $this->tenantId)->where('status', 'active')->get(['id', 'name']),
        ];

        if ($request->has('contact_id')) {
            $contact = Contact::where('tenant_id', $this->tenantId)->find($request->contact_id);
            if ($contact) {
                $recService = new \App\Services\CRM\RecommendationService();
                $data['recommendations'] = $recService->getRecommendations($contact);
                $relMapper = new \App\Services\CRM\RelationshipMapper();
                $data['graph_data'] = $relMapper->getGraphData($contact);
                $data['selected_contact'] = $contact->load(['deals', 'quotes.items', 'tickets', 'activities' => function($q) {
                    $q->with('creator')->latest();
                }]);
            }
        }

        return $data;
    }

    private function loadCompanies()
    {
        return [
            'accounts' => Account::where('tenant_id', $this->tenantId)
                ->withCount(['contacts', 'deals'])
                ->with(['creator'])
                ->latest()
                ->get(),
        ];
    }

    private function loadSegments()
    {
        return [
            'segments' => ContactSegment::where('tenant_id', $this->tenantId)
                ->withCount('contacts')
                ->latest()
                ->get(),
        ];
    }

    private function loadDuplicates()
    {
        return [
            'duplicates' => (new \App\Services\CRM\ContactMatchingService)->findDuplicates($this->tenantId),
        ];
    }

    private function loadImports()
    {
        return [
            'imports' => ContactImport::where('tenant_id', $this->tenantId)
                ->latest()
                ->take(20)
                ->get(),
        ];
    }

    private function loadPartners()
    {
        return [
            'partners' => Partner::where('tenant_id', $this->tenantId)
                ->withCount(['leads', 'referrals'])
                ->latest()
                ->get(),
        ];
    }
}
