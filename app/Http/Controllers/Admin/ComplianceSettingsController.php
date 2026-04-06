<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ComplianceRule;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class ComplianceSettingsController extends Controller
{
    public function index()
    {
        // Fetch current active rules for all components
        $rules = [
            'PF' => ComplianceRule::getRule('PF')?->rules_json,
            'ESI' => ComplianceRule::getRule('ESI')?->rules_json,
            'PT' => ComplianceRule::getRule('PT')?->rules_json, // Just fetching one for now, logic might vary
        ];

        return Inertia::render('Admin/Compliance/Settings', [
            'initialRules' => $rules
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'component' => 'required|in:PF,ESI,PT',
            'rules' => 'required|array',
            'effective_from' => 'nullable|date'
        ]);

        // Create a NEW rule version instead of overwriting history
        // This ensures retro-payroll still works with old rules if needed.
        
        ComplianceRule::create([
            'component' => $request->component,
            'effective_from' => $request->effective_from ?? now(),
            'rules_json' => $request->rules,
            'is_active' => true,
            'description' => 'Updated via Helper UI on ' . now()->toDateString()
        ]);

        return back()->with('success', $request->component . ' Rules updated successfully.');
    }
}
