<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\SystemSetting;
use App\Models\TaxRegime;
use App\Models\TaxSection;
use App\Models\TaxSlab;

class TaxConfigurationController extends Controller
{
    public function index()
    {
        // 1. Fetch System Settings for Tax
        $settings = SystemSetting::where('group', 'tax')->get()->pluck('value', 'key');
        
        // 2. Fetch Regimes & Slabs
        $regimes = TaxRegime::with('slabs')->get();
        
        // 3. Fetch Sections
        $sections = TaxSection::all();
        
        return Inertia::render('HR/Settings/TaxConfiguration', [
            'settings' => $settings,
            'regimes' => $regimes,
            'sections' => $sections
        ]);
    }

    /**
     * Store General Settings (Tab A & D)
     */
    public function storeSettings(Request $request)
    {
        $data = $request->validate([
            'financial_year' => 'required|string',
            'declaration_window_start' => 'nullable|date',
            'declaration_window_end' => 'nullable|date',
            'proof_window_start' => 'nullable|date',
            'proof_window_end' => 'nullable|date',
            'default_regime' => 'required|in:Old,New',
            'metro_cities' => 'nullable|string', // Comma separated
            'rent_receipt_limit' => 'nullable|numeric',
        ]);

        foreach ($data as $key => $value) {
            SystemSetting::updateOrCreate(
                ['group' => 'tax', 'key' => $key],
                ['value' => $value]
            );
        }
        
        // Update default regime
        TaxRegime::where('name', 'Old')->update(['is_default' => $data['default_regime'] === 'Old']);
        TaxRegime::where('name', 'New')->update(['is_default' => $data['default_regime'] === 'New']);

        return back()->with('success', 'Tax Settings Updated');
    }

    /**
     * Store Slabs (Tab B)
     */
    public function storeSlabs(Request $request)
    {
        $request->validate([
            'regime_id' => 'required|exists:tax_regimes,id',
            'slabs' => 'array',
            'slabs.*.min_income' => 'required|numeric',
            'slabs.*.tax_rate_percentage' => 'required|numeric'
        ]);

        // Replace Slabs
        TaxSlab::where('regime_id', $request->regime_id)->delete();
        
        foreach ($request->slabs as $slab) {
            TaxSlab::create([
                'regime_id' => $request->regime_id,
                'min_income' => $slab['min_income'],
                'max_income' => $slab['max_income'] ?? null,
                'tax_rate_percentage' => $slab['tax_rate_percentage']
            ]);
        }

        return back()->with('success', 'Tax Slabs Updated');
    }

    /**
     * Store Limits (Tab C)
     */
    public function storeLimits(Request $request)
    {
        $request->validate([
            'sections' => 'array',
            'sections.*.id' => 'required|exists:tax_sections,id',
            'sections.*.max_deduction' => 'nullable|numeric',
            'sections.*.is_active' => 'boolean'
        ]);

        foreach ($request->sections as $s) {
            TaxSection::where('id', $s['id'])->update([
                'max_deduction' => $s['max_deduction'],
                'is_active' => $s['is_active']
            ]);
        }

        return back()->with('success', 'Section Limits Updated');
    }
}
