<?php

namespace App\Services\CRM\Loaders;

use Illuminate\Http\Request;
use App\Models\CRM\EmailAccount;
use App\Models\CRM\EmailThread;
use App\Models\CRM\AutomationRule;
use App\Models\CRM\CampaignJourney;

class CommunicationLoader extends SectionLoader
{
    public function load(string $tab, Request $request): array
    {
        return match ($tab) {
            'inbox' => $this->loadInbox($request),
            'sent' => $this->loadSent($request),
            'drafts' => $this->loadDrafts($request),
            'automations' => $this->loadAutomations(),
            'campaigns' => $this->loadCampaigns(),
            'settings' => $this->loadSettings(),
            default => $this->loadInbox($request),
        };
    }

    private function loadInbox(Request $request): array
    {
        $query = EmailThread::where('tenant_id', $this->tenantId)
            ->with(['latestMessage', 'messages' => fn($q) => $q->latest()->limit(1)])
            ->withCount('messages');

        if ($request->filled('account_id')) {
            $query->where('email_account_id', $request->account_id);
        }

        return [
            'threads' => $query->latest('updated_at')->paginate(20),
            'accounts' => EmailAccount::where('tenant_id', $this->tenantId)->get(),
        ];
    }

    private function loadSent(Request $request): array
    {
        // Similar to inbox but filtered for sent threads if needed, 
        // or just re-use inbox with different sort/filter
        return $this->loadInbox($request);
    }

    private function loadDrafts(Request $request): array
    {
        return [
            'drafts' => [], // To be implemented
            'accounts' => EmailAccount::where('tenant_id', $this->tenantId)->get(),
        ];
    }

    private function loadAutomations(): array
    {
        return [
            'rules' => AutomationRule::where('tenant_id', $this->tenantId)->get(),
            'triggers' => [
                'lead_created', 'contact_created', 'deal_won', 'email_opened', 'meeting_scheduled'
            ]
        ];
    }

    private function loadCampaigns(): array
    {
        return [
            'journeys' => CampaignJourney::where('tenant_id', $this->tenantId)->withCount('steps')->get(),
        ];
    }

    private function loadSettings(): array
    {
        return [
            'accounts' => EmailAccount::where('tenant_id', $this->tenantId)->get(),
        ];
    }
}
