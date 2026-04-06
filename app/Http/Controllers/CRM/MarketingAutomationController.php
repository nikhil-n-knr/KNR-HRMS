<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use App\Models\CRM\MarketingAutomation;
use App\Models\CRM\AutomationStep;
use Illuminate\Http\Request;

class MarketingAutomationController extends Controller
{
    public function index()
    {
        $tenantId = auth()->user()->tenant_id;
        $automations = MarketingAutomation::where('tenant_id', $tenantId)
            ->withCount(['steps', 'executions as active_executions_count' => function($q) {
                $q->where('status', 'active');
            }])
            ->latest()
            ->get();

        return response()->json($automations);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'trigger_type' => 'required|string',
        ]);

        $automation = MarketingAutomation::create([
            'tenant_id' => auth()->user()->tenant_id,
            'name' => $request->name,
            'description' => $request->description,
            'trigger_type' => $request->trigger_type,
            'is_active' => true,
        ]);

        return response()->json($automation, 201);
    }

    public function addStep(Request $request, MarketingAutomation $automation)
    {
        $request->validate([
            'type' => 'required|in:action,condition,delay',
            'step_order' => 'required|integer',
        ]);

        $step = $automation->steps()->create($request->all());

        return response()->json($step, 201);
    }

    public function toggle(MarketingAutomation $automation)
    {
        $automation->update(['is_active' => !$automation->is_active]);
        return response()->json(['message' => 'Automation state toggled', 'is_active' => $automation->is_active]);
    }

    public function destroy(MarketingAutomation $automation)
    {
        $automation->delete();
        return response()->json(['message' => 'Automation deleted']);
    }
}
