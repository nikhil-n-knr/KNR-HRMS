<?php

namespace App\Services\CRM\Loaders;

use Illuminate\Http\Request;
use App\Models\CRM\Ticket;
use App\Models\CRM\KbCategory;

class SupportLoader extends SectionLoader
{
    public function load(string $tab, Request $request): array
    {
        return match ($tab) {
            'all_tickets', 'tickets' => $this->loadTickets(),
            'my_tickets' => $this->loadMyTickets(),
            'queue' => $this->loadQueue(),
            'kb' => $this->loadKb(),
            default => [],
        };
    }

    private function loadTickets(): array
    {
        return [
            'tickets' => \App\Models\CRM\Ticket::where('tenant_id', $this->tenantId)
                ->with(['contact', 'assignee'])
                ->latest()
                ->get(),
            'contacts' => \App\Models\CRM\Contact::where('tenant_id', $this->tenantId)
                ->select('id', 'first_name', 'last_name')
                ->get()
        ];
    }

    private function loadMyTickets()
    {
        return [
            'tickets' => Ticket::where('tenant_id', $this->tenantId)
                ->where('assigned_to', auth()->id())
                ->with(['contact:id,first_name,last_name', 'assignee:id,name'])
                ->latest()
                ->get(),
            'contacts' => \App\Models\CRM\Contact::where('tenant_id', $this->tenantId)->select('id', 'first_name', 'last_name')->get(),
        ];
    }

    private function loadQueue()
    {
        return [
            'tickets' => Ticket::where('tenant_id', $this->tenantId)
                ->whereNull('assigned_to')
                ->with(['contact:id,first_name,last_name', 'assignee:id,name'])
                ->latest()
                ->get(),
            'contacts' => \App\Models\CRM\Contact::where('tenant_id', $this->tenantId)->select('id', 'first_name', 'last_name')->get(),
        ];
    }

    private function loadKb()
    {
        return [
            'kb_categories' => KbCategory::where('tenant_id', $this->tenantId)
                ->withCount('articles')
                ->get(),
            'kb_articles' => \App\Models\CRM\KbArticle::where('tenant_id', $this->tenantId)
                ->with('category:id,name')
                ->latest()
                ->take(50)
                ->get(),
            'popular_articles' => \App\Models\CRM\KbArticle::where('tenant_id', $this->tenantId)
                ->orderByDesc('views')
                ->take(5)
                ->get(),
        ];
    }
}
