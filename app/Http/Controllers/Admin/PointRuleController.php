<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\PointRule;

class PointRuleController extends Controller
{
    /**
     * Display the Gamification Rule Manager.
     */
    public function index()
    {
        $rules = PointRule::orderBy('created_at', 'desc')->paginate(15);

        return Inertia::render('Admin/Gamification/RuleManager', [
            'rules' => $rules
        ]);
    }

    /**
     * Store a newly created rule.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'event_key' => 'required|string|unique:point_rules,event_key|max:50',
            'points' => 'required|integer',
            'description' => 'nullable|string',
        ]);

        PointRule::create($request->all());

        return back()->with('success', 'Rule created successfully.');
    }

    /**
     * Update the specified rule.
     */
    public function update(Request $request, PointRule $pointRule) // Route-Model Binding uses {point_rule} usually, usually 'rule' in route?
    {
        // Actually, route resource uses naming convention. Let's assume standard resource 'gamification'.
        
        $request->validate([
            'name' => 'required|string|max:255',
            'points' => 'required|integer',
            'description' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        $pointRule->update($request->only(['name', 'points', 'description', 'is_active']));

        return back()->with('success', 'Rule updated.');
    }

    /**
     * Remove (delete) the rule.
     */
    public function destroy($id)
    {
        $rule = PointRule::findOrFail($id);
        $rule->delete();
        return back()->with('success', 'Rule deleted.');
    }
}
