<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use App\Models\CRM\Contact;
use App\Models\CRM\Account;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class ContactController extends Controller
{
    /**
     * Display a listing of contacts.
     */
    public function index(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;
        
        $query = Contact::where('tenant_id', $tenantId)
            ->with(['account', 'creator']);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Filter by Account
        if ($request->filled('account_id')) {
            $query->where('account_id', $request->account_id);
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $contacts = $query->paginate(20);

        return Inertia::render('CRM/Contacts/Index', [
            'contacts' => $contacts,
            'filters' => $request->only(['search', 'account_id']),
        ]);
    }

    protected $automationEngine;

    public function __construct(\App\Services\CRM\AutomationEngine $automationEngine)
    {
        $this->automationEngine = $automationEngine;
    }

    /**
     * Store a newly created contact.
     */
    public function store(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => [
                'required', 'email', 'max:255',
                Rule::unique('crm_contacts')->where(fn ($query) => $query->where('tenant_id', $tenantId))
            ],
            // ... rest of validation remains same
            'secondary_email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'work_phone' => ['nullable', 'string', 'max:20'],
            'mobile' => ['nullable', 'string', 'max:20'],
            'account_id' => ['nullable', 'exists:crm_accounts,id'],
            'title' => ['nullable', 'string', 'max:100'],
            'department' => ['nullable', 'string', 'max:100'],
            'linkedin_url' => ['nullable', 'url', 'max:255'],
            'twitter_handle' => ['nullable', 'string', 'max:255'],
            'social_links' => ['nullable', 'array'],
            'date_of_birth' => ['nullable', 'date'],
            'address' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'addresses' => ['nullable', 'array'],
            'addresses.*.type' => ['required', 'string', 'in:mailing,billing,home,work'],
            'addresses.*.street' => ['required', 'string'],
        ]);

        $contact = Contact::create([
            ...$validated,
            'tenant_id' => $tenantId,
            'created_by' => auth()->id(),
        ]);

        if ($request->has('addresses')) {
            $contact->addresses()->createMany($request->addresses);
        }

        // Dispatch Automation
        $this->automationEngine->dispatch('contact_created', $contact);

        return redirect()
            ->back()
            ->with('success', 'Contact created successfully and enrolled in automation.');
    }

    /**
     * Display the specified contact.
     */
    public function show(Contact $contact)
    {
        $contact->load([
            'account',
            'creator',
            'addresses',
            'deals' => fn($q) => $q->latest(),
            'activities' => fn($q) => $q->latest()->limit(10)
        ]);

        return Inertia::render('CRM/Contacts/Show', [
            'contact' => $contact,
            'users' => \App\Models\User::where('tenant_id', $contact->tenant_id)->select('id', 'name')->get(),
        ]);
    }

    /**
     * Update the specified contact.
     */
    public function update(Request $request, Contact $contact)
    {
        $tenantId = auth()->user()->tenant_id;

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => [
                'required', 'email', 'max:255',
                Rule::unique('crm_contacts')->ignore($contact->id)->where(fn ($query) => $query->where('tenant_id', $tenantId))
            ],
            'secondary_email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'work_phone' => ['nullable', 'string', 'max:20'],
            'mobile' => ['nullable', 'string', 'max:20'],
            'account_id' => ['nullable', 'exists:crm_accounts,id'],
            'title' => ['nullable', 'string', 'max:100'],
            'department' => ['nullable', 'string', 'max:100'],
            'linkedin_url' => ['nullable', 'url', 'max:255'],
            'twitter_handle' => ['nullable', 'string', 'max:255'],
            'social_links' => ['nullable', 'array'],
            'date_of_birth' => ['nullable', 'date'],
            'address' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'addresses' => ['nullable', 'array'],
        ]);

        $contact->update($validated);

        if ($request->has('addresses')) {
            $contact->addresses()->delete();
            $contact->addresses()->createMany($request->addresses);
        }

        return redirect()
            ->back()
            ->with('success', 'Contact updated successfully.');
    }

    /**
     * Remove the specified contact.
     */
    public function destroy(Contact $contact)
    {
        $contact->delete();

    }

    /**
     * Transfer ownership of the contact.
     */
    public function transfer(Request $request, Contact $contact)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'reason' => 'nullable|string|max:500',
        ]);

        $oldOwnerId = $contact->created_by; 

        \App\Models\CRM\HandoverLog::create([
            'tenant_id' => $contact->tenant_id,
            'trackable_type' => get_class($contact),
            'trackable_id' => $contact->id,
            'from_user_id' => $oldOwnerId,
            'to_user_id' => $validated['user_id'],
            'reason' => $validated['reason'],
        ]);

        return redirect()->back()->with('success', 'Contact ownership record updated.');
    }
}
