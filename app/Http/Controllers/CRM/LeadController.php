<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use App\Models\CRM\Lead;
use App\Models\CRM\Contact;
use App\Models\CRM\Account;
use App\Models\User;
use App\Http\Requests\CRM\StoreLeadRequest;
use App\Http\Requests\CRM\UpdateLeadRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LeadController extends Controller
{
    protected $assignmentService;
    protected $scoringService;
    protected $automationEngine;

    public function __construct(
        \App\Services\CRM\LeadAssignmentService $assignmentService,
        \App\Services\CRM\LeadScoringService $scoringService,
        \App\Services\CRM\AutomationEngine $automationEngine
    ) {
        $this->assignmentService = $assignmentService;
        $this->scoringService = $scoringService;
        $this->automationEngine = $automationEngine;
    }

    /**
     * Display a listing of leads.
     */
    public function index(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;
        
        $query = Lead::where('tenant_id', $tenantId)
            ->with(['convertedToAccount', 'creator']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('company', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('source')) {
            $query->where('source', $request->source);
        }
        if ($request->filled('min_score')) {
            $query->where('score', '>=', $request->min_score);
        }
        
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $leads = $query->paginate(20);

        return Inertia::render('CRM/Leads/Index', [
            'leads' => $leads,
            'filters' => $request->only(['search', 'status', 'source', 'min_score']),
        ]);
    }

    /**
     * Store a newly created lead.
     */
    public function store(StoreLeadRequest $request)
    {
        $lead = Lead::create([
            ...$request->validated(),
            'tenant_id' => auth()->user()->tenant_id,
            'created_by' => auth()->id(),
        ]);

        // Auto-assign and Score
        $this->assignmentService->assign($lead);
        $this->scoringService->calculate($lead);

        // Dispatch Automation
        $this->automationEngine->dispatch('lead_created', $lead);

        return redirect()
            ->back()
            ->with('success', 'Lead captured and processed.');
    }

    /**
     * Display the specified lead.
     */
    public function show(Lead $lead)
    {
        $lead->load([
            'convertedToAccount',
            'convertedToContact',
            'creator',
            'activities' => function($query) {
                $query->latest()->limit(10);
            }
        ]);

        return Inertia::render('CRM/Leads/Show', [
            'lead' => $lead,
            'users' => \App\Models\User::where('tenant_id', $lead->tenant_id)->select('id', 'name')->get(),
        ]);
    }

    /**
     * Update the specified lead.
     */
    public function update(UpdateLeadRequest $request, Lead $lead)
    {
        $lead->update($request->validated());
        
        // Recalculate score on update
        $this->scoringService->calculate($lead);
        
        return redirect()->back()->with('success', 'Lead updated and re-scored.');
    }

    /**
     * Remove the specified lead.
     */
    public function destroy(Lead $lead)
    {
        $lead->delete();
        return redirect()->back()->with('success', 'Lead removed.');
    }

    /**
     * Convert lead to contact.
     */
    public function convert(Request $request, Lead $lead)
    {
        $request->validate([
            'account_id' => ['nullable', 'exists:crm_accounts,id'],
            'create_account' => ['nullable', 'boolean'],
        ]);

        $accountId = $request->account_id;

        // Auto-create account if requested or if lead has company and no account selected
        if (!$accountId && ($request->create_account || $lead->company)) {
            $account = Account::create([
                'tenant_id' => $lead->tenant_id,
                'name' => $lead->company ?? ($lead->first_name . ' ' . $lead->last_name . ' (Personal)'),
                'industry' => 'Other',
                'created_by' => auth()->id(),
            ]);
            $accountId = $account->id;
        }

        // Create contact from lead
        $contact = Contact::create([
            'tenant_id' => $lead->tenant_id,
            'account_id' => $accountId,
            'first_name' => $lead->first_name,
            'last_name' => $lead->last_name,
            'email' => $lead->email,
            'phone' => $lead->phone,
            'title' => $lead->title,
            'city' => $lead->city,
            'country' => $lead->country,
            'created_by' => auth()->id(),
        ]);

        // Link lead to contact and mark as converted
        $lead->update([
            'converted_to_contact_id' => $contact->id,
            'converted_to_account_id' => $accountId,
            'converted_at' => now(),
            'status' => 'converted',
        ]);

        // Transfer activities to contact
        $lead->activities()->update([
            'activityable_type' => Contact::class,
            'activityable_id' => $contact->id,
        ]);

        return redirect()
            ->route('crm.hub', ['section' => 'contacts', 'tab' => 'all_contacts', 'contact_id' => $contact->id])
            ->with('success', 'Lead successfully graduated to Contact.');
    }

    /**
     * Bulk action on leads.
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => ['required', 'in:delete,update_status,assign'],
            'lead_ids' => ['required', 'array'],
            'lead_ids.*' => ['exists:crm_leads,id'],
            'status' => ['required_if:action,update_status'],
            'assigned_to' => ['required_if:action,assign', 'exists:users,id'],
        ]);

        $tenantId = auth()->user()->tenant_id;
        $leads = Lead::where('tenant_id', $tenantId)
            ->whereIn('id', $request->lead_ids);

        switch ($request->action) {
            case 'delete':
                $leads->delete();
                $message = 'Leads deleted successfully.';
                break;
                
            case 'update_status':
                $leads->update(['status' => $request->status]);
                $message = 'Lead status updated successfully.';
                break;
                
            case 'assign':
                $leads->update(['assigned_to' => $request->assigned_to]);
                $message = 'Leads assigned successfully.';
                break;
        }

    }

    /**
     * Transfer ownership of the lead.
     */
    public function transfer(Request $request, Lead $lead)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'reason' => 'nullable|string|max:500',
        ]);

        $oldOwnerId = $lead->assigned_to;
        $lead->update(['assigned_to' => $validated['user_id']]);

        \App\Models\CRM\HandoverLog::create([
            'tenant_id' => $lead->tenant_id,
            'trackable_type' => get_class($lead),
            'trackable_id' => $lead->id,
            'from_user_id' => $oldOwnerId,
            'to_user_id' => $validated['user_id'],
            'reason' => $validated['reason'],
        ]);

        // Dispatch Automation
        $this->automationEngine->dispatch('lead_transferred', $lead);

        return redirect()->back()->with('success', 'Lead ownership transferred successfully.');
    }
}
