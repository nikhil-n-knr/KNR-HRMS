<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use App\Models\CRM\Deal;
use App\Models\CRM\PipelineStage;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DealController extends Controller
{
    /**
     * Display a listing of deals.
     */
    public function index(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;
        
        $query = Deal::where('tenant_id', $tenantId)
            ->with(['account', 'contact', 'assignee', 'stage']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%");
        }

        if ($request->filled('stage_id')) {
            $query->where('stage_id', $request->stage_id);
        }

        if ($request->filled('status')) { // won, lost, open
            if ($request->status === 'won') $query->where('stage', 'won');
            elseif ($request->status === 'lost') $query->where('stage', 'lost');
            else $query->whereNotIn('stage', ['won', 'lost']);
        }

        $query->latest();

        return Inertia::render('CRM/Deals/Index', [
            'deals' => $query->paginate(20),
            'stages' => PipelineStage::where('tenant_id', $tenantId)->orderBy('order')->get(),
            'filters' => $request->only(['search', 'stage_id', 'status']),
        ]);
    }

    /**
     * Store a newly created deal.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0'],
            'account_id' => ['required', 'exists:crm_accounts,id'],
            'contact_id' => ['nullable', 'exists:crm_contacts,id'],
            'stage_id' => ['required', 'exists:crm_pipeline_stages,id'],
            'closing_date' => ['nullable', 'date'],
            'probability' => ['integer', 'min:0', 'max:100'],
        ]);

        Deal::create([
            ...$validated,
            'tenant_id' => auth()->user()->tenant_id,
            'created_by' => auth()->id(),
            'value' => $validated['amount'], // Mapping amount to value column
            'stage' => 'open', // Internal stage status
        ]);

        return redirect()
            ->route('crm.deals.index')
            ->with('success', 'Deal created successfully.');
    }

    /**
     * Display the specified deal.
     */
    public function show(Deal $deal)
    {
        $deal->load(['account', 'contact', 'assignee', 'activities' => fn($q) => $q->latest()]);

        return Inertia::render('CRM/Deals/Show', [
            'deal' => $deal,
            'stages' => PipelineStage::where('tenant_id', $deal->tenant_id)->orderBy('order')->get(),
        ]);
    }

    /**
     * Update the specified deal.
     */
    public function update(Request $request, Deal $deal)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0'],
            'stage_id' => ['required', 'exists:crm_pipeline_stages,id'],
            'closing_date' => ['nullable', 'date'],
            'probability' => ['integer', 'min:0', 'max:100'],
        ]);

        $deal->update([
            ...$validated,
            'value' => $validated['amount'],
        ]);

        return redirect()
            ->back()
            ->with('success', 'Deal updated successfully.');
    }

    /**
     * Update deal stage (Kanban drag-drop).
     */
    public function updateStage(Request $request, Deal $deal)
    {
        $request->validate([
            'stage_id' => ['required', 'exists:crm_pipeline_stages,id'],
        ]);

        $deal->update(['stage_id' => $request->stage_id]);

        return back();
    }

    /**
     * Mark deal as Won.
     */
    public function markAsWon(Deal $deal)
    {
        $deal->update(['stage' => 'won', 'probability' => 100]);
        return back()->with('success', 'Deal marked as Won!');
    }

    /**
     * Mark deal as Lost.
     */
    public function markAsLost(Request $request, Deal $deal)
    {
        $request->validate(['reason' => 'nullable|string']);
        
        $deal->update([
            'stage' => 'lost', 
            'probability' => 0,
            'loss_reason' => $request->reason
        ]);
        
        return back()->with('success', 'Deal marked as Lost.');
    }

    /**
     * Remove the specified deal.
     */
    public function destroy(Deal $deal)
    {
        $deal->delete();
        return redirect()->route('crm.deals.index')->with('success', 'Deal deleted successfully.');
    }
}
