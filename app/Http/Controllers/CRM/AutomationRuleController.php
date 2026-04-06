<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use App\Models\CRM\AutomationRule;
use Illuminate\Http\Request;

class AutomationRuleController extends Controller
{
    public function index()
    {
        $tenantId = auth()->user()->tenant_id;
        return response()->json(AutomationRule::where('tenant_id', $tenantId)->get());
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'trigger_event' => 'required|string',
            'actions' => 'required|array',
        ]);

        $rule = AutomationRule::create([
            'tenant_id' => auth()->user()->tenant_id,
            'name' => $request->name,
            'trigger_event' => $request->trigger_event,
            'actions' => $request->actions,
            'is_active' => true,
        ]);

        return redirect()->back()->with('success', 'Automation Rule created and activated.');
    }

    public function update(Request $request, AutomationRule $automationRule)
    {
        $automationRule->update($request->all());
        return response()->json($automationRule);
    }

    public function destroy(AutomationRule $automationRule)
    {
        $automationRule->delete();
        return response()->json(['message' => 'Rule deleted']);
    }
}
