<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use App\Models\CRM\Quote; // Updated namespace
use App\Models\CRM\Deal;
use App\Models\CRM\Contact;
use App\Models\CRM\Account;
use Illuminate\Http\Request;
use Inertia\Inertia;

class QuoteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;
        
        $query = Quote::where('tenant_id', $tenantId)
            ->with(['deal', 'contact', 'account', 'creator']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('quote_number', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%");
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $quotes = $query->latest()->paginate(15);

        return Inertia::render('CRM/Quotes/Index', [
            'quotes' => $quotes,
            'filters' => $request->only(['search', 'status']),
            'products' => \App\Models\CRM\Product::where('tenant_id', $tenantId)->select('id', 'name', 'base_price', 'description')->get(),
            'accounts' => Account::where('tenant_id', $tenantId)->select('id', 'name')->get(), // Ensure accounts are passed for the modal too
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Fetch deals, contacts, accounts for dropdowns
         $tenantId = auth()->user()->tenant_id;
         
         return Inertia::render('CRM/Quotes/Create', [
             'deals' => Deal::where('tenant_id', $tenantId)->select('id', 'name')->get(),
             'contacts' => Contact::where('tenant_id', $tenantId)->select('id', 'first_name', 'last_name')->get(),
             'accounts' => Account::where('tenant_id', $tenantId)->select('id', 'name')->get(),
         ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, \App\Services\CRM\QuoteService $quoteService)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'deal_id' => 'nullable|exists:crm_deals,id',
            'contact_id' => 'nullable|exists:crm_contacts,id',
            'account_id' => 'nullable|exists:crm_accounts,id',
            'valid_until' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'nullable|exists:crm_products,id',
            'items.*.name' => 'required|string',
            'items.*.description' => 'nullable|string',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'tax_rate' => 'nullable|numeric|min:0',
        ]);

        $tenantId = auth()->user()->tenant_id;
        
        $quote = Quote::create([
            'tenant_id' => $tenantId,
            'quote_number' => $quoteService->generateQuoteNumber($tenantId),
            'title' => $validated['title'],
            'deal_id' => $validated['deal_id'],
            'contact_id' => $validated['contact_id'],
            'account_id' => $validated['account_id'],
            'valid_until' => $validated['valid_until'],
            'status' => 'draft',
            'tax_rate' => $validated['tax_rate'] ?? 0,
            'created_by' => auth()->id(),
        ]);

        foreach ($validated['items'] as $index => $item) {
            $quote->items()->create([
                'product_id' => $item['product_id'] ?? null,
                'name' => $item['name'],
                'description' => $item['description'] ?? null,
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'total' => $item['quantity'] * $item['unit_price'],
                'order' => $index,
            ]);
        }

        // Totals are auto-calculated by QuoteItem model boot events
        
        return redirect()->route('crm.hub', ['section' => 'sales', 'tab' => 'quotes'])->with('success', 'Quote created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Quote $quote)
    {
         $quote->load(['items', 'deal', 'contact', 'account', 'creator']);
         return Inertia::render('CRM/Quotes/Show', ['quote' => $quote]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Quote $quote)
    {
        // Implementation for update similar to store
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Quote $quote)
    {
        $quote->delete();
        return back()->with('success', 'Quote deleted.');
    }
}
