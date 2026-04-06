<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\JobApplication;
use App\Models\Candidate;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class ReferralController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Show candidates referred by the current user
        $referrals = JobApplication::with(['candidate', 'job'])
            ->where('referrer_id', auth()->id())
            ->latest()
            ->paginate(10);

        return Inertia::render('Employee/Referrals/Index', [
            'referrals' => $referrals
        ]);
    }

    /**
     * Search for candidates to refer (Retroactive)
     */
    public function search(Request $request)
    {
        $query = $request->input('query');
        if (strlen($query) < 3) return response()->json([]);

        // Find Candidates who have applications but NO referrer yet
        $results = JobApplication::with(['candidate', 'job'])
            ->whereNull('referrer_id')
            ->whereHas('candidate', function($q) use ($query) {
                $q->where('first_name', 'like', "%{$query}%")
                  ->orWhere('last_name', 'like', "%{$query}%")
                  ->orWhere('email', 'like', "%{$query}%");
            })
            ->take(10)
            ->get()
            ->map(function ($app) {
                return [
                    'id' => $app->id, // Application ID to link
                    'candidate_name' => $app->candidate->first_name . ' ' . $app->candidate->last_name,
                    'candidate_email' => $app->candidate->email,
                    'job_title' => $app->job->title,
                    'applied_at' => $app->created_at->format('M d, Y'),
                    'status' => $app->status
                ];
            });

        return response()->json($results);
    }

    /**
     * Store a newly created referral (Link existing application).
     */
    public function store(Request $request)
    {
        $request->validate([
            'application_id' => 'required|exists:job_applications,id'
        ]);

        $app = JobApplication::findOrFail($request->application_id);

        if ($app->referrer_id) {
            return back()->with('error', 'Candidate is already referred by someone else.');
        }

        $app->update([
            'referrer_id' => auth()->id()
        ]);

        return back()->with('success', 'Referral linked successfully!');
    }
}
