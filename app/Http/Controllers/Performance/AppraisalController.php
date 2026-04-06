<?php

namespace App\Http\Controllers\Performance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Performance\Appraisal;
use App\Models\Performance\GoalRating;
use App\Models\Performance\AppraisalCycle;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AppraisalController extends Controller
{
    public function team()
    {
        $user = Auth::user();
        $managerId = $user->id; // Assuming user_id is linked to reporting_to in employees table

        // fetch employees reporting to this user
        // Then fetch their active cycle appraisal
        $activeCycle = AppraisalCycle::where('is_active', true)->first();
        
        if (!$activeCycle) {
             return Inertia::render('Performance/Team/Index', ['appraisals' => []]);
        }

        $appraisals = Appraisal::with('employee')
            ->where('appraisal_cycle_id', $activeCycle->id)
            ->whereHas('employee', function($q) use ($managerId) {
                $q->where('reporting_to', $managerId);
            })
            ->get();

        return Inertia::render('Performance/Team/Index', [
            'appraisals' => $appraisals,
            'cycle' => $activeCycle
        ]);
    }

    public function show(Appraisal $appraisal)
    {
        $appraisal->load(['employee', 'cycle', 'goalRatings.goal']);

        // Check Permissions (Self or Manager)
        // $this->authorize('view', $appraisal); 

        return Inertia::render('Performance/Appraisals/Show', [
            'appraisal' => $appraisal
        ]);
    }

    public function update(Request $request, Appraisal $appraisal)
    {
        // Simple Logic: If 'Self Review', update self fields. If 'Manager Review', update manager fields.
        // In real app, separate FormRequests are better.
        
        if ($appraisal->stage === 'Self Review') {
            $validated = $request->validate([
                'self_comments' => 'nullable|string',
                'ratings' => 'array', 
                'ratings.*.goal_id' => 'required|exists:goals,id',
                'ratings.*.self_rating' => 'required|numeric|min:1|max:5',
                'ratings.*.self_remarks' => 'nullable|string'
            ]);

            // Save Overall Comments
            $appraisal->update(['self_comments' => $validated['self_comments']]);

            // Save Ratings
            foreach ($validated['ratings'] as $rating) {
                GoalRating::updateOrCreate(
                    ['appraisal_id' => $appraisal->id, 'goal_id' => $rating['goal_id']],
                    ['self_rating' => $rating['self_rating'], 'self_remarks' => $rating['self_remarks']]
                );
            }
        }
        elseif ($appraisal->stage === 'Manager Review') {
            $validated = $request->validate([
                'manager_comments' => 'nullable|string',
                'ratings' => 'array',
                'ratings.*.goal_id' => 'required|exists:goals,id',
                'ratings.*.manager_rating' => 'required|numeric|min:1|max:5',
                'ratings.*.manager_remarks' => 'nullable|string'
            ]);

            $appraisal->update(['manager_comments' => $validated['manager_comments']]);

            foreach ($validated['ratings'] as $rating) {
                GoalRating::updateOrCreate(
                    ['appraisal_id' => $appraisal->id, 'goal_id' => $rating['goal_id']],
                    ['manager_rating' => $rating['manager_rating'], 'manager_remarks' => $rating['manager_remarks']]
                );
            }
        }

        return back()->with('success', 'Appraisal Saved.');
    }

    public function submit(Request $request, Appraisal $appraisal)
    {
        if ($appraisal->stage === 'Self Review') {
            $appraisal->update([
                'stage' => 'Manager Review',
                'submitted_at' => Carbon::now()
            ]);
        } elseif ($appraisal->stage === 'Manager Review') {
            $appraisal->update([
                'stage' => 'HR Review', // or Closed
                'reviewed_at' => Carbon::now()
            ]);
        }

        return back()->with('success', 'Appraisal Submitted.');
    }
}
