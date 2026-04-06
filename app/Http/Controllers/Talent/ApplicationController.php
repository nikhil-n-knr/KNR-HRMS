<?php

namespace App\Http\Controllers\Talent;

use App\Http\Controllers\Controller;
use App\Models\Candidate;
use App\Models\JobApplication;
use App\Models\JobPosting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Illuminate\Support\Str;
use App\Services\Infrastructure\LoggerService;

class ApplicationController extends Controller
{
    protected $logger;

    public function __construct(LoggerService $logger)
    {
        $this->logger = $logger;
    }
    /**
     * Show the public job-board or a specific job.
     */
    public function index(Request $request) 
    {
        $query = JobPosting::with(['department', 'location'])->where('status', 'Published')->latest();

        // Filters can be added here (search, dept, etc.)
        // For now, let frontend filter since the dataset is smallish (or add basic search)
        
        return Inertia::render('Talent/Public/JobBoard', [
            'jobs' => $query->get(),
            'departments' => \App\Models\Department::has('jobs')->select('id', 'name')->get(),
            'locations' => \App\Models\Location::has('jobs')->select('id', 'name', 'city')->get()
        ]);
    }

    public function show(JobPosting $job)
    {
        if ($job->status !== 'Published') {
            abort(404);
        }

        return Inertia::render('Talent/Public/JobView', [
            'job' => $job->load(['department', 'location', 'screeningTemplate']),
        ]);
    }

    /**
     * Handle the application submission.
     */
    public function store(Request $request, JobPosting $job)
    {
        // 1. Basic Validation
        $rules = [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'resume' => 'required|file|mimes:pdf,docx|max:5120', // 5MB
            'cover_letter' => 'nullable|string|max:5000',
            'answers' => 'nullable|array', // Dynamic answers
        ];

        // 2. Dynamic Validation from Template
        if ($job->screeningTemplate) {
            foreach ($job->screeningTemplate->questions as $q) {
                if (!empty($q['validation']['required']) && $q['validation']['required']) {
                     // Note: 'answers' is an array keyed by question ID. 
                     // e.g. answers['q_123'] = 'My Answer'
                     $rules["answers.{$q['id']}"] = 'required'; 
                }
            }
        }

        $request->validate($rules);

        // 3. Restriction: Check for duplicate applications in the last 3 months
        // Check by Email OR Phone
        $hasRecentApplication = JobApplication::where('job_posting_id', $job->id)
            ->whereHas('candidate', function ($q) use ($request) {
                $q->where('email', $request->email)
                  ->orWhere('phone', $request->phone);
            })
            ->where('created_at', '>=', now()->subMonths(3))
            ->exists();

        if ($hasRecentApplication) {
            return back()->withErrors(['email' => 'You have already applied for this position within the last 3 months.']);
        }

        DB::beginTransaction();
        try {
            // 3. Handle Candidate (Create or Update)
            // We use email as the unique identifier.
            $candidate = Candidate::firstOrCreate(
                ['email' => $request->email],
                [
                    'first_name' => $request->first_name,
                    'last_name' => $request->last_name,
                    'phone' => $request->phone,
                ]
            );

            // Update resume if provided (Logic: Always update latest? Or keep history? generic overwrite for now)
            if ($request->hasFile('resume')) {
                $path = $request->file('resume')->store('resumes/' . date('Y/m'), 'public');
                $candidate->update(['resume_path' => $path]);
            }

            // 4. Create Application
            /* (Restriction already checked above) */

            $app = JobApplication::create([
                'job_posting_id' => $job->id,
                'candidate_id' => $candidate->id,
                'status' => 'Applied',
                'cover_letter' => $request->cover_letter,
                'answers' => $request->answers,
                // 'video_answers' => ... (TODO: Handle blobs)
            ]);

            DB::commit();

            $this->logger->log('recruitment', 'application_submission', 'Candidate applied: ' . $candidate->email, ['job_id' => $job->id, 'application_id' => $app->id]);

            return redirect()->back()->with('success', 'Application submitted successfully! Good luck.')->setStatusCode(303);

        } catch (\Exception $e) {
            DB::rollBack();
            $this->logger->log('recruitment', 'application_submission_error', 'Application Failed', ['error' => $e->getMessage(), 'job_id' => $job->id], 'error');
            return back()->withErrors(['error' => 'Something went wrong. Please try again.']);
        }
    }
}
