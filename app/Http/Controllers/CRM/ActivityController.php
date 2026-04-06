<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use App\Models\CRM\Activity;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ActivityController extends Controller
{
    /**
     * Store a newly created activity.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'], // This will store the rich text content or notes
            'type' => ['required', 'in:call,email,meeting,note,task'],
            'due_date' => ['nullable', 'date'],
            'activityable_type' => ['required', 'string'],
            'activityable_id' => ['required', 'integer'],
        ]);

        Activity::create([
            'tenant_id' => auth()->user()->tenant_id,
            'type' => $validated['type'],
            'subject' => $validated['subject'],
            'description' => $validated['description'],
            'due_date' => $validated['due_date'] ?? null,
            'activityable_type' => $validated['activityable_type'],
            'activityable_id' => $validated['activityable_id'],
            'created_by' => auth()->id(),
            // 'assigned_to' => $request->assigned_to, // Optional: add if needed later
        ]);

        return back()->with('success', 'Activity logged.');
    }

    /**
     * Mark activity as complete.
     */
    public function complete(Activity $activity)
    {
        $activity->update(['completed_at' => now()]);
        return back();
    }

    /**
     * Remove the specified activity.
     */
    public function destroy(Activity $activity)
    {
        $activity->delete();
        return back()->with('success', 'Activity deleted.');
    }
}
