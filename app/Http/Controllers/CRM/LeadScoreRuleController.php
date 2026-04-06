<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use App\Models\CRM\LeadScoreRule;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LeadScoreRuleController extends Controller
{
    public function index()
    {
        $tenantId = auth()->user()->tenant_id;
        $rules = LeadScoreRule::where('tenant_id', $tenantId)->get();
        
        return Inertia::render('CRM/Leads/Scoring', [
            'rules' => $rules
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'criteria_field' => 'required|string',
            'operator' => 'required|string',
            'value' => 'required|string',
            'points' => 'required|integer',
        ]);

        LeadScoreRule::create([
            'tenant_id' => auth()->user()->tenant_id,
            ...$request->all()
        ]);

        return back()->with('success', 'Rule created successfully');
    }

    public function destroy(LeadScoreRule $rule)
    {
        $rule->delete();
        return back()->with('success', 'Rule deleted successfully');
    }
}
