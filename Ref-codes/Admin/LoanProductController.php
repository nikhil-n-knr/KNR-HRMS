<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LoanProduct;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LoanProductController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Loans/Products/Index', [
            'products' => LoanProduct::with('interestRules')->get()
        ]);
    }

    public function getSettings()
    {
        return response()->json([
            'employee_loans_enabled' => (bool) \App\Models\SystemSetting::get('employee_loans_enabled', true)
        ]);
    }

    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'employee_loans_enabled' => 'required|boolean'
        ]);

        \App\Models\SystemSetting::set('employee_loans_enabled', $validated['employee_loans_enabled'], 'loans');

        return redirect()->back()->with('success', 'Loan Policy Settings updated successfully.');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:loan_products,name',
            'description' => 'nullable|string',
            'interest_type' => 'required|in:Flat,Reducing',
            'max_amount_limit' => 'required|numeric|min:0',
            'max_tenure_months' => 'required|integer|min:1',
            'eligibility_multiplier' => 'required|numeric|min:0',
            'is_active' => 'boolean',
            'rules' => 'array|nullable',
            'rules.*.min_amount' => 'required|numeric|min:0',
            'rules.*.max_amount' => 'nullable|numeric|gte:rules.*.min_amount',
            'rules.*.min_tenure_months' => 'required|integer|min:0',
            'rules.*.max_tenure_months' => 'nullable|integer|gte:rules.*.min_tenure_months',
            'rules.*.interest_rate' => 'required|numeric|min:0'
        ]);

        $product = LoanProduct::create($validated);

        if (!empty($validated['rules'])) {
            $product->interestRules()->createMany($validated['rules']);
        }

        return redirect()->back()->with('success', 'Loan Product created successfully.');
    }

    public function update(Request $request, LoanProduct $loanProduct)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:loan_products,name,' . $loanProduct->id,
            'description' => 'nullable|string',
            'interest_type' => 'required|in:Flat,Reducing',
            'max_amount_limit' => 'required|numeric|min:0',
            'max_tenure_months' => 'required|integer|min:1',
            'eligibility_multiplier' => 'required|numeric|min:0',
            'is_active' => 'boolean',
            'rules' => 'array|nullable',
            'rules.*.min_amount' => 'required|numeric|min:0',
            'rules.*.max_amount' => 'nullable|numeric|gte:rules.*.min_amount',
            'rules.*.min_tenure_months' => 'required|integer|min:0',
            'rules.*.max_tenure_months' => 'nullable|integer|gte:rules.*.min_tenure_months',
            'rules.*.interest_rate' => 'required|numeric|min:0'
        ]);

        $loanProduct->update($validated);

        // Sync rules: Delete all and recreate (Simpler than diffing for now)
        $loanProduct->interestRules()->delete();
        if (!empty($validated['rules'])) {
            $loanProduct->interestRules()->createMany($validated['rules']);
        }

        return redirect()->back()->with('success', 'Loan Product updated successfully.');
    }

    public function destroy(LoanProduct $loanProduct)
    {
        $loanProduct->delete();
        return redirect()->back()->with('success', 'Loan Product deleted successfully.');
    }
}
