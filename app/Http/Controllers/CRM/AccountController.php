<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use App\Models\CRM\Account;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class AccountController extends Controller
{
    /**
     * Display a listing of accounts.
     */
    public function index(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;
        
        $query = Account::where('tenant_id', $tenantId)
            ->withCount(['contacts', 'deals']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('industry', 'like', "%{$search}%");
        }

        $accounts = $query->latest()->paginate(20);

        return Inertia::render('CRM/Accounts/Index', [
            'accounts' => $accounts,
            'filters' => $request->only(['search']),
        ]);
    }

    /**
     * Store a newly created account.
     */
    public function store(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        $validated = $request->validate([
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('crm_accounts')->where(fn ($query) => $query->where('tenant_id', $tenantId))
            ],
            'industry' => ['nullable', 'string', 'max:100'],
            'website' => ['nullable', 'url', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'description' => ['nullable', 'string'],
        ]);

        Account::create([
            ...$validated,
            'tenant_id' => $tenantId,
            'created_by' => auth()->id(),
        ]);

        return redirect()
            ->back()
            ->with('success', 'Account created successfully.');
    }

    /**
     * Display the specified account.
     */
    public function show(Account $account)
    {
        $account->load([
            'contacts',
            'deals' => fn($q) => $q->with('contact')->latest(),
            'activities' => fn($q) => $q->latest()->limit(10),
            'creator'
        ]);

        return Inertia::render('CRM/Accounts/Show', [
            'account' => $account,
        ]);
    }

    /**
     * Update the specified account.
     */
    public function update(Request $request, Account $account)
    {
        $tenantId = auth()->user()->tenant_id;

        $validated = $request->validate([
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('crm_accounts')->ignore($account->id)->where(fn ($query) => $query->where('tenant_id', $tenantId))
            ],
            'industry' => ['nullable', 'string', 'max:100'],
            'website' => ['nullable', 'url', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'description' => ['nullable', 'string'],
        ]);

        $account->update($validated);

        return redirect()
            ->back()
            ->with('success', 'Account updated successfully.');
    }

    /**
     * Remove the specified account.
     */
    public function destroy(Account $account)
    {
        $account->delete();

        return redirect()
            ->back()
            ->with('success', 'Account deleted successfully.');
    }
}
