<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\TaxRegime;
use App\Models\TaxSlab;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TaxSettingController extends Controller
{
    /**
     * Display the Tax Settings page (Regimes & Slabs).
     */
    public function index()
    {
        return Inertia::render('HR/Payroll/TaxSettings', [
            'regimes' => TaxRegime::with('slabs')->get()
        ]);
    }

    /**
     * Store a new Tax Regime.
     */
    public function storeRegime(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'is_default' => 'boolean'
        ]);

        if ($request->is_default) {
            // Unset other defaults
            TaxRegime::where('is_default', true)->update(['is_default' => false]);
        }

        TaxRegime::create($request->all());

        return back()->with('success', 'Tax Regime created successfully.')->setStatusCode(303);
    }

    /**
     * Store a new Tax Slab.
     */
    public function storeSlab(Request $request)
    {
        $request->validate([
            'regime_id' => 'required|exists:tax_regimes,id',
            'min_income' => 'required|numeric|min:0',
            'max_income' => 'nullable|numeric|gt:min_income',
            'tax_rate_percentage' => 'required|numeric|min:0|max:100',
        ]);

        TaxSlab::create($request->all());

        return back()->with('success', 'Tax Slab added successfully.')->setStatusCode(303);
    }

    /**
     * Update a Tax Slab.
     */
    public function updateSlab(Request $request, TaxSlab $slab)
    {
        $request->validate([
            'min_income' => 'required|numeric|min:0',
            'max_income' => 'nullable|numeric|gt:min_income',
            'tax_rate_percentage' => 'required|numeric|min:0|max:100',
        ]);

        $slab->update($request->all());

        return back()->with('success', 'Tax Slab updated.')->setStatusCode(303);
    }

    /**
     * Delete a Tax Slab.
     */
    public function destroySlab(TaxSlab $slab)
    {
        $slab->delete();
        return back()->with('success', 'Tax Slab removed.')->setStatusCode(303);
    }
}
