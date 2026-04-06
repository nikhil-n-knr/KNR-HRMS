<?php

namespace App\Http\Controllers\Performance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Performance\Goal;
use App\Models\Performance\AppraisalCycle;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class GoalController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $employee = $user->employee; 

        // Get Active Cycle
        $activeCycle = AppraisalCycle::where('is_active', true)->first();

        return Inertia::render('Performance/Goals/Index', [
            'goals' => $activeCycle 
                ? Goal::where('employee_id', $employee->id)
                      ->where('appraisal_cycle_id', $activeCycle->id)
                      ->get() 
                : [],
            'cycle' => $activeCycle ?? null
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'weightage' => 'required|integer|min:1|max:100',
            'appraisal_cycle_id' => 'required|exists:appraisal_cycles,id'
        ]);

        $employee = Auth::user()->employee;

        Goal::create([
            'employee_id' => $employee->id,
            'appraisal_cycle_id' => $validated['appraisal_cycle_id'],
            'title' => $validated['title'],
            'description' => $validated['description'],
            'weightage' => $validated['weightage'],
            'status' => 'Draft' // Default
        ]);

        return redirect()->back()->with('success', 'Goal Created (Draft).');
    }

    public function update(Request $request, Goal $goal)
    {
        $this->authorize('update', $goal); // ensure ownership

        if ($goal->status !== 'Draft' && $goal->status !== 'Rejected') {
            return back()->with('error', 'Cannot edit locked goals.');
        }

        $validated = $request->validate([
            'title' => 'required|string',
            'description' => 'required|string',
            'weightage' => 'required|integer'
        ]);

        $goal->update($validated);

        return back()->with('success', 'Goal Updated.');
    }

    // Manager Action
    public function approve(Request $request, Goal $goal)
    {
        // Add Gate/Policy check later: $this->authorize('approve', $goal);
        
        $validated = $request->validate([
            'status' => 'required|in:Approved,Rejected',
            'manager_remarks' => 'nullable|string'
        ]);

        $goal->update([
            'status' => $validated['status'],
            'manager_remarks' => $validated['manager_remarks']
        ]);

        return back()->with('success', 'Goal Status Updated.');
    }
}
