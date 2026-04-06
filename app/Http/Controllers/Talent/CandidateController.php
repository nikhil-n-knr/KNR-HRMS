<?php

namespace App\Http\Controllers\Talent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Candidate;
use App\Models\JobPosting;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use App\Services\Infrastructure\LoggerService;
use Illuminate\Support\Facades\Mail;
use App\Models\Interview;
use App\Models\JobApplication;
use App\Models\User;
use App\Models\Team;
use App\Models\DocumentTemplate;
use App\Models\SalaryStructure;
use App\Mail\InterviewInvitation;
use App\Mail\InterviewRescheduled;
use App\Mail\InterviewReminder;
// use App\Mail\InterviewAssigned; // Replaced by Notification
use App\Mail\InterviewCancelled;
use App\Notifications\Talent\InterviewScheduled;
use App\Notifications\Talent\InterviewCancelledNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class CandidateController extends Controller
{
    protected $logger;

    public function __construct(LoggerService $logger)
    {
        $this->logger = $logger;
    }

    public function index(Request $request)
    {
        // For Kanban, we need candidates grouped by status
        // OR we fetch all and let frontend group them.
        // Given typically < 1000 candidates active, frontend grouping is fine.
        // Statuses: Applied, Screening, Interview, Offer, Hired, Rejected
        
        $query = Candidate::with(['applications.job', 'applications.interviews.feedbacks', 'applications.offerLetter', 'applications.referrer']) // Eager load active applications & feedback
            ->whereHas('applications', function($q) {
                $q->where('status', '!=', 'Rejected'); // Filter out archived/rejected if needed
            });

        $selectedJob = null;
        if ($request->has('job_id')) {
             $selectedJob = JobPosting::find($request->job_id);
             $query->whereHas('applications', function($q) use ($request) {
                 $q->where('job_posting_id', $request->job_id);
             });
        }

        if ($request->has('search')) {
            $query->where(function($q) use ($request) {
                $q->where('first_name', 'like', '%' . $request->search . '%')
                  ->orWhere('last_name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }
        
        // Scope Filter
        if ($request->scope === 'assigned') {
            // Screening assignments
            $query->whereHas('applications', function($q) {
                 $userId = auth()->id();
                 // Simple JSON check for now
                 $q->whereJsonContains('reviewers', (string)$userId);
            });
        } elseif ($request->scope === 'interviews') {
            // My Interviews
            $query->whereHas('applications.interviews', function($q) {
                $q->where('interviewer_id', auth()->id());
            });
        }

        $candidates = $query->paginate(15)->withQueryString();
        
        $candidates->getCollection()->transform(function($candidate) {
            // Flatten for Kanban card
            // Prioritize Active Applications
            $app = $candidate->applications->where('status', '!=', 'Rejected')->sortByDesc('updated_at')->first();
            if (!$app) {
                // Fallback to latest including rejected if no active one found
                $app = $candidate->applications->sortByDesc('created_at')->first();
            }
            
            // Extract latest feedback if available
            $latestInterview = $app ? $app->interviews->sortByDesc('created_at')->first() : null;
            $feedback = $latestInterview ? $latestInterview->feedbacks->first() : null;
            
            // Check for Pending Feedback for Auth User
            $pendingFeedback = false;
            if ($app) {
                $userInterview = $app->interviews->where('interviewer_id', auth()->id())->where('status', 'Scheduled')->first();
                if ($userInterview) {
                     $hasFeedback = $userInterview->feedbacks()->where('interviewer_id', auth()->id())->exists();
                     if (!$hasFeedback) $pendingFeedback = true;
                }
            }

            // Normalize Status for Kanban
            $rawStatus = $app ? $app->status : 'Applied';
            $status = $rawStatus;
            if (in_array($rawStatus, ['Pending_Approval', 'Draft', 'Sent', 'Viewed', 'Released', 'Pending_Docs'])) {
                $status = 'Offer';
            }

            return [
                'id' => $candidate->id,
                'name' => $candidate->first_name . ' ' . $candidate->last_name,
                'email' => $candidate->email,
                'status' => $status,
                'raw_status' => $rawStatus, // Keep original for details if needed
                'job_title' => $app ? $app->job->title : 'N/A',
                'score' => $app ? $app->score : null,
                'interview_rating' => $feedback ? $feedback->rating : null,
                'interview_recommendation' => $feedback ? $feedback->recommendation : null,
                'last_interview_round' => $latestInterview ? $latestInterview->round : null,
                'last_interview_date' => $latestInterview ? $latestInterview->scheduled_at->format('M d, Y') : null,
                'last_interview_status' => $latestInterview ? $latestInterview->status : 'None',
                'pending_feedback' => $pendingFeedback, 
                'rejection_reason' => ($app && $app->status === 'Rejected') ? $app->rejection_reason : null,
                'background_status' => $candidate->background_check_status,
                'stage_config' => $app ? $app->job->stage_config : null, // Pass config
                'screening_rating' => $candidate->screening_rating, // Exposed field
                'application_id' => $app ? $app->id : null,
                'application_id' => $app ? $app->id : null,
                'created_at' => $candidate->created_at->diffForHumans(),
                'referrer' => $app && $app->referrer ? [
                    'name' => $app->referrer->name,
                    'email' => $app->referrer->email
                ] : null,
                'latest_offer' => $app && $app->offerLetter ? [
                    'id' => $app->offerLetter->id,
                    'status' => $app->offerLetter->status,
                    'token' => $app->offerLetter->token,
                    'otp' => $app->offerLetter->otp,
                    'salary_amount' => $app->offerLetter->salary_amount,
                    'salary_currency' => $app->offerLetter->salary_currency,
                    'designation' => $app->offerLetter->designation,
                    'joining_date' => $app->offerLetter->joining_date,
                    'offer_date' => $app->offerLetter->offer_date,
                    'expiry_date' => $app->offerLetter->expiry_date,
                    'salary_breakdown' => $app->offerLetter->salary_breakdown, // JSON Cast
                    'is_conditional' => $app->offerLetter->is_conditional,
                    'sent_at' => $app->offerLetter->created_at->format('M d, h:i A'), // Proxy for Sent Time
                    'accepted_at' => $app->offerLetter->accepted_at ? \Carbon\Carbon::parse($app->offerLetter->accepted_at)->format('M d, h:i A') : null,
                    'is_draft' => $app->offerLetter->is_draft,
                    'is_force_release' => $app->offerLetter->is_force_release,
                    'template_ids' => $app->offerLetter->template_ids,
                    'approvers' => $app->offerLetter->approver_ids ?? [], // IDs
                    'email_config' => $app->offerLetter->email_config,
                    'documents' => $app->offerLetter->documents ? $app->offerLetter->documents->map(function($d) {
                         return [
                             'id' => $d->id,
                             'name' => $d->name,
                             'status' => $d->status,
                             'file_path' => $d->file_path,
                             'is_mandatory' => $d->is_mandatory,
                             'stage' => $d->stage,
                             'rejection_reason' => $d->rejection_reason
                         ];
                    }) : []
                ] : null
            ];
        });

        // Fetch Auxiliary Data for Modals
        $jobs = JobPosting::select('id', 'title')->where('status', 'Published')->get();
        $users = User::select('id', 'name', 'email')->get(); 
        $teams = Team::select('id', 'name')->get();
        // Templates and Structures for Offer Modal
        $offerTemplates = DocumentTemplate::where('type', 'Offer_Letter')->orWhere('type', 'Offer')->select('id', 'name')->get();
        $salaryStructures = SalaryStructure::with('components')->select('id', 'name')->get();

        return Inertia::render('Talent/Candidates/Index', [
            'candidates' => $candidates,
            'jobs' => $jobs,
            'selectedJob' => $selectedJob, // For Board Config
            'users' => $users,
            'teams' => $teams,
            'offerTemplates' => $offerTemplates,
            'salaryStructures' => $salaryStructures, 
            'filters' => $request->only(['job_id', 'search', 'scope']),
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        
        try {
            DB::beginTransaction();

            // Move candidate to new column
            $candidate = Candidate::findOrFail($id);
            $app = $candidate->applications()->first(); // Primary application
            $oldStatus = $app->status;

            if ($app) {
                // 1. Handle Interview Workflow
                if ($request->status === 'Interview' && !$request->boolean('skip_workflow')) {
                    $this->handleInterviewWorkflow($app, $request);
                }

                // 2. Handle Screening Workflow (Assignments)
                if ($request->status === 'Screening' && !$request->boolean('skip_workflow')) {
                    if ($request->has('reviewer_ids')) {
                         $app->reviewers = $request->input('reviewer_ids');
                    }
                    if ($request->has('team_ids')) {
                         $app->assigned_teams = $request->input('team_ids');
                    }
                }
                
                // Allow updating assignments even if status doesn't change (Edit Mode)
                if ($request->status === $app->status) {
                     if ($request->has('reviewer_ids')) $app->reviewers = $request->input('reviewer_ids');
                     if ($request->has('team_ids')) $app->assigned_teams = $request->input('team_ids');
                }

                // 3. Update Status
                $app->status = $request->status;
                $app->save();
                

                
                $this->logger->log('recruitment', 'candidate_status_update', 'Candidate Status Updated: ' . $app->status, [
                    'candidate_id' => $id, 
                    'old' => $oldStatus, 
                    'new' => $request->status,
                    'assignments_updated' => $request->has('reviewer_ids') || $request->has('team_ids')
                ]);
            }
            
            DB::commit();
            DB::commit();
            return back()->with('success', 'Candidate moved to ' . $request->status)->setStatusCode(303);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            throw $e;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->logger->log('recruitment', 'candidate_status_update_error', 'Candidate Move Failed', ['error' => $e->getMessage()], 'error');
            return back()->withErrors(['error' => 'Failed to update status: ' . $e->getMessage()]);
        }
    }

    private function handleInterviewWorkflow($app, Request $request)
    {
        // Check for Schedule Later
        if ($request->input('interview.schedule_mode') === 'later') {
            // Log intention? Maybe add a flag? For now, we just allow the status change.
            return;
        }

        $data = $request->validate([
            'interview.scheduled_at' => 'required|date|after:now',
            'interview.duration' => 'required|integer|min:15',
            'interview.interviewer_ids' => 'required|array',
            'interview.type' => 'required|string',
        ]);

        $interviewData = $data['interview'];

        // Create Main Interview Record (Assigned to first interviewer or auth user)
        $interview = $app->interviews()->create([
            'interviewer_id' => $interviewData['interviewer_ids'][0] ?? auth()->id(),
            'scheduled_at' => $interviewData['scheduled_at'],
            'duration' => $interviewData['duration'],
            'type' => $interviewData['type'],
            'status' => 'Scheduled',
            'round' => $request->input('interview.round', 'Round 1'),
            'location' => $request->input('interview.location'),
            'meeting_link' => $request->input('interview.meeting_link'),
        ]);

        // $cc = $request->input('interview.cc');
        // Mail::to(...)->cc($cc)->send(...)
    }

    public function storeInterview(Request $request, $applicationId)
    {

        $data = $request->validate([
            'scheduled_at' => 'required|date|after:now',
            'duration' => 'required|integer|min:15',
            'interviewer_ids' => 'required|array',
            'type' => 'required|string',
            'round' => 'required|string',
            'round_title' => 'nullable|string',
            'location' => 'nullable|string',
            'meeting_link' => 'nullable|string',
            'message_body' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();
            $app = \App\Models\JobApplication::findOrFail($applicationId);

            $interview = $app->interviews()->create([
                'interviewer_id' => $data['interviewer_ids'][0] ?? auth()->id(),
                'scheduled_at' => $data['scheduled_at'],
                'duration' => $data['duration'],
                'type' => $data['type'],
                'status' => 'Scheduled',
                'round' => $data['round'],
                'round_title' => $data['round_title'],
                'location' => $data['location'],
                'meeting_link' => $data['meeting_link'],
                'message_body' => $data['message_body'],
            ]);



            $this->logger->log('recruitment', 'interview_scheduled', 'Interview Scheduled', ['interview_id' => $interview->id, 'candidate_id' => $app->candidate_id]);

            // Update Application Status
            if ($app->status !== 'Interview') {
                $app->update(['status' => 'Interview']);
            }

            // Send Invitation Email to Candidate
            try {
                Mail::to($app->candidate->email)
                    ->send(new InterviewInvitation($interview, $data['message_body']));

            } catch (\Exception $e) {
                // Log failure but don't block
            }

            // Send Assignment Notification to Interviewer (Database + Email)
             try {
                if ($interview->interviewer) {
                    Notification::send($interview->interviewer, new InterviewScheduled($interview));
                }
            } catch (\Exception $e) {
                $this->logger->log('recruitment', 'interviewer_notification_error', 'Interviewer Notification Failed', ['error' => $e->getMessage()], 'error');
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            $this->logger->log('recruitment', 'interview_schedule_error', 'Interview Schedule Failed', ['error' => $e->getMessage()], 'error');
            return back()->withErrors(['error' => 'Failed to schedule interview.']);
        }
    }

    public function updateInterview(Request $request, Interview $interview)
    {

         $data = $request->validate([
            'scheduled_at' => 'required|date',
            'duration' => 'required|integer|min:15',
            'type' => 'required|string',
            'round_title' => 'nullable|string',
            'location' => 'nullable|string',
            'meeting_link' => 'nullable|string',
            'message_body' => 'nullable|string',
            'status' => 'nullable|string|in:Scheduled,Completed,Cancelled'
         ]);

         $originalScheduledAt = $interview->scheduled_at;
         
         $interview->update($data);

         // Check for Reschedule & Email
         if ($request->boolean('send_reschedule_email') && $originalScheduledAt != $data['scheduled_at']) {
             try {
                 Mail::to($interview->application->candidate->email)
                     ->send(new InterviewRescheduled($interview, $data['message_body']));
             } catch (\Exception $e) {
                 $this->logger->log('recruitment', 'reschedule_email_error', 'Reschedule Email Failed', ['error' => $e->getMessage()], 'error');
             }
         }

         // Auto-Close Notifications if Completed or Cancelled
         if (in_array($data['status'] ?? '', ['Completed', 'Cancelled'])) {
             // Find notifications for this interview
             // Assuming we want to clear it for the Auth user (who likely performed the action)
             // or typically the interviewer.
             $user = auth()->user();
             $user->notifications()
                  ->where('data->interview_id', $interview->id)
                  ->whereNull('read_at')
                  ->update(['read_at' => now()]);
         }

         return back()->with('success', 'Interview details updated.')->setStatusCode(303);
    }

  
    public function sendReminder(Interview $interview)
    {

        try {
            // Send Logic
            Mail::to($interview->application->candidate->email)
                ->send(new InterviewReminder($interview));
            
            $interview->increment('reminder_count');
            $interview->update(['last_reminder_sent_at' => now()]);

            // Append to history
            $history = $interview->reminder_history ?? [];
            $history[] = [
                'sent_at' => now()->toIso8601String(),
                'sent_by' => auth()->id()
            ];
            $interview->reminder_history = $history;
            $interview->save();

            $interview->save();

            return back()->with('success', 'Reminder sent successfully.')->setStatusCode(303);

        } catch (\Exception $e) {
        } catch (\Exception $e) {
            $this->logger->log('recruitment', 'interview_reminder_error', 'Reminder Failed', ['error' => $e->getMessage()], 'error');
              return back()->withErrors(['error' => 'Failed to send reminder.']);
        }
    }

    public function reject(Request $request, JobApplication $application)
    {
        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:255',
            'rejection_notes' => 'nullable|string',
            'notify_candidate' => 'boolean'
        ]);

        $application->update([
            'status' => 'Rejected',
            'rejection_reason' => $validated['rejection_reason'],
            'rejection_notes' => $validated['rejection_notes'] ?? null
        ]);

        if ($request->boolean('notify_candidate')) {
            try {
                \Illuminate\Support\Facades\Notification::route('mail', $application->candidate->email)
                    ->notify(new \App\Notifications\Talent\CandidateRejected($application, $validated['rejection_reason']));
            } catch (\Exception $e) {
                // Log but don't fail the request
                $this->logger->log('recruitment', 'notification_error', 'Rejection Email Failed', ['error' => $e->getMessage()], 'warning');
            }
        }

        $this->logger->log('recruitment', 'candidate_rejected', 'Candidate Rejected', ['application_id' => $application->id, 'reason' => $validated['rejection_reason']]);

        return back()->with('success', 'Candidate rejected successfully.')->setStatusCode(303);
    }

    public function offer(Request $request, JobApplication $application)
    {
        $validated = $request->validate([
            'salary' => 'required|numeric',
            'start_date' => 'required|date',
            'expiry_date' => 'nullable|date|after:today',
            'notes' => 'nullable|string'
        ]);

        $application->update([
            'status' => 'Offer',
            'offer_details' => $validated
        ]);


        $this->logger->log('recruitment', 'candidate_moved_to_offer', 'Candidate Moved to Offer Stage', ['application_id' => $application->id]);

        return back()->with('success', 'Offer recorded successfully.')->setStatusCode(303);
    }

    public function rateScreening(Request $request, Candidate $candidate)
    {
        $data = $request->validate([
            'rating' => 'nullable|integer|min:1|max:5',
            'feedback' => 'nullable|string|max:1000',
            'result' => 'nullable|string|in:Interview,Rejected,Hold'
        ]);

        $application = $candidate->applications()->where('status', '!=', 'Rejected')->latest()->firstOrFail();
        
        $updateData = [];
        if (array_key_exists('rating', $data)) {
            $updateData['screening_rating'] = $data['rating'];
        }
        if (array_key_exists('feedback', $data)) {
            $updateData['screening_feedback'] = $data['feedback'];
        }

        // Handle Result / Status Change
        if (!empty($data['result']) && $data['result'] !== 'Hold') {
             $updateData['status'] = $data['result'];
             
             // If Rejected, maybe add default reason? 
             if ($data['result'] === 'Rejected') {
                 $updateData['rejection_reason'] = 'Screening Rejection';
             }
             
             // Log the move
             // Log the move
             $this->logger->log('recruitment', 'screening_status_update', 'Candidate Status Updated via Screening', [
                 'candidate_id' => $candidate->id, 
                 'new_status' => $data['result']
             ]);
        }

        if (!empty($updateData)) {
            $application->update($updateData);
        }

        return back()->with('success', 'Screening feedback updated.');
    }

    public function storeFeedback(Request $request, Interview $interview)
    {
        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'recommendation' => 'required|string|in:Strong Hire,Hire,No Hire,Strong No,On Hold',
            'summary' => 'nullable|string',
            'pros' => 'nullable|array',
            'cons' => 'nullable|array',
            'recording_url' => 'nullable|url',
            'attachment' => 'nullable|file|max:51200' // 50MB max
        ]);

        // Handle file upload
        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('recordings/interviews', 'public');
        }

        $feedback = $interview->feedbacks()->updateOrCreate(
            ['interviewer_id' => auth()->id()],
            [
                'rating' => $validated['rating'],
                'recommendation' => $validated['recommendation'],
                'summary' => $validated['summary'] ?? null,
                'pros' => $validated['pros'] ?? [],
                'cons' => $validated['cons'] ?? [],
                'recording_url' => $validated['recording_url'] ?? null,
                'attachment_path' => $attachmentPath
            ]
        );

        // Update Interview Result if provided (or infer from recommendation?)
        // For now, let's allow explicit result or infer.
        // If "Strong Hire" or "Hire", result = 'Passed'. If "No Hire", result = 'Failed'.
        if ($request->filled('result')) {
             $interview->update(['result' => $request->result]);
        } else {
             // Auto-infer
             if (in_array($validated['recommendation'], ['Strong Hire', 'Hire'])) {
                 $interview->update(['result' => 'Passed']);
             } elseif (in_array($validated['recommendation'], ['No Hire', 'Strong No'])) {
                 $interview->update(['result' => 'Failed']);
             }
        }
               $this->logger->log('recruitment', 'interview_feedback', 'Interview Feedback Stored', ['interview_id' => $interview->id, 'feedback_id' => $feedback->id]);

        return back()->with('success', 'Feedback submitted successfully.')->setStatusCode(303);

        }

 

    public function cancelInterview(Request $request, Interview $interview)
    {

         $validated = $request->validate([
             'cancellation_reason' => 'required|string|max:255'
         ]);

         $interview->update([
             'status' => 'Cancelled',
             'cancellation_reason' => $validated['cancellation_reason']
         ]);

         // Notify Candidate & Interviewer
         try {
             // To Candidate (Email Only)
             Mail::to($interview->application->candidate->email)
                 ->send(new InterviewCancelled($interview, $validated['cancellation_reason']));
             
             // To Interviewer (Notification: DB + Email)
             if($interview->interviewer) {
                 Notification::send($interview->interviewer, new InterviewCancelledNotification($interview, $validated['cancellation_reason']));
             }
         } catch (\Exception $e) {

             $this->logger->log('recruitment', 'interview_cancel_notification_error', 'Cancellation Notification Failed', ['error' => $e->getMessage()], 'error');
         }

         return back()->with('success', 'Interview cancelled successfully.')->setStatusCode(303);
    }

    public function quickView(Request $request, Candidate $candidate)
    {
        // If not an AJAX/Inertia request, redirect to Index with open_id
        if (!$request->wantsJson() && !$request->header('X-Inertia')) {
            return to_route('talent.candidates.index', ['open_id' => $candidate->id]);
        }

        // Eager load necessary relationships
        $candidate->load([
            'applications.job', 
            'applications.interviews.interviewer', 
            'applications.interviews.feedbacks',
            'applications.offerLetter.documents',
            'applications.interviews' => function($q) {
                $q->orderBy('scheduled_at', 'desc');
            }
        ]);

        $app = $candidate->applications->first();

        // Construct Timeline (Dummy + Real)
        // ideally fetching from ActivityLog or derivating from timestamps
        $timeline = [];
        
        $timeline[] = [
            'id' => 'created',
            'content' => 'Applied to ' . ($app->job->title ?? 'Job'),
            'date' => $candidate->created_at->diffForHumans()
        ];
        
        if ($app && $app->status !== 'Applied') {
            $timeline[] = [
                'id' => 'status_change',
                'content' => 'Moved to ' . $app->status,
                'date' => $app->updated_at->diffForHumans()
            ];
        }

        // Add Interviews to Timeline
        if ($app) {
            foreach($app->interviews as $interview) {
                // Scheduled Event
                $timeline[] = [
                    'id' => 'interview_scheduled_' . $interview->id,
                    'content' => "{$interview->round} Scheduled ({$interview->type})",
                    'date' => $interview->created_at->diffForHumans(),
                    'icon' => 'calendar',
                    'color' => 'blue'
                ];

                // Completion Event
                if ($interview->status === 'Completed') {
                     $timeline[] = [
                        'id' => 'interview_completed_' . $interview->id,
                        'content' => "{$interview->round} Completed",
                        'date' => $interview->updated_at->diffForHumans(), // Approx
                        'icon' => 'check',
                        'color' => 'green'
                    ];
                }

                // Cancellation Event
                if ($interview->status === 'Cancelled') {
                     $timeline[] = [
                        'id' => 'interview_cancelled_' . $interview->id,
                        'content' => "{$interview->round} Cancelled: " . Str::limit($interview->cancellation_reason, 30),
                        'date' => $interview->updated_at->diffForHumans(),
                        'icon' => 'x',
                        'color' => 'red'
                    ];
                }

                // Feedback Event
                foreach($interview->feedbacks as $feedback) {
                    $interviewerName = $feedback->interviewer ? $feedback->interviewer->name : 'Interviewer';
                    $timeline[] = [
                        'id' => 'feedback_' . $feedback->id,
                        'content' => "Feedback from {$interviewerName}: {$feedback->recommendation}",
                        'date' => $feedback->created_at->diffForHumans(),
                        'icon' => 'chat',
                        'color' => 'purple'
                    ];
                }
            }
        }

        // Sort by Date (We need absolute dates for sorting, then map to diffForHumans if we want relative)
        // For simplicity, let's trust array_reverse logic if we built it chronologically, but real events happen at different times.
        // Better: Use a sortable date field.
        
        // Add timestamp to all for sorting
        foreach ($timeline as &$event) {
             // Re-parse date for sorting if needed, or use original object timestamps if we passed them.
             // Since we only passed string 'diffForHumans', sorting is hard. 
             // Let's refactor to use real dates for sorting.
        }
        
        // FIX: Re-building timeline with sortable keys
        $timelineEvents = collect();

        $timelineEvents->push([
            'id' => 'created',
            'content' => 'Applied to ' . ($app->job->title ?? 'Job'),
            'date_string' => $candidate->created_at->diffForHumans(),
            'timestamp' => $candidate->created_at,
            'icon' => 'user-add',
            'color' => 'gray'
        ]);

        if ($app && $app->status !== 'Applied') {
             $timelineEvents->push([
                'id' => 'status_latest', // Only tracks latest status change timestamp effectively
                'content' => 'Current Status: ' . $app->status,
                'date_string' => $app->updated_at->diffForHumans(),
                'timestamp' => $app->updated_at,
                'icon' => 'refresh',
                'color' => 'indigo'
            ]);
        }

        if ($app) {
            foreach($app->interviews as $interview) {
                $timelineEvents->push([
                    'id' => 'interview_sch_' . $interview->id,
                    'content' => "{$interview->round} Scheduled ({$interview->type})",
                    'date_string' => $interview->created_at->diffForHumans(),
                    'timestamp' => $interview->created_at,
                    'icon' => 'calendar',
                    'color' => 'blue'
                ]);

                if ($interview->status === 'Completed') {
                     $timelineEvents->push([
                        'id' => 'interview_comp_' . $interview->id,
                        'content' => "{$interview->round} Completed",
                        'date_string' => $interview->updated_at->diffForHumans(),
                        'timestamp' => $interview->updated_at,
                        'icon' => 'check',
                        'color' => 'green'
                    ]);
                }
                
                // Feedbacks
                foreach($interview->feedbacks as $feedback) {
                     $interviewerName = $feedback->interviewer ? $feedback->interviewer->name : 'Interviewer';
                     $timelineEvents->push([
                        'id' => 'feedback_' . $feedback->id,
                        'content' => "Feedback: {$feedback->recommendation} ({$interviewerName})",
                        'date_string' => $feedback->created_at->diffForHumans(),
                        'timestamp' => $feedback->created_at,
                        'icon' => 'chat',
                        'color' => 'purple'
                    ]);
                }
            }
        }

        // Sort Descending
        $timeline = $timelineEvents->sortByDesc('timestamp')->map(function($item) {
            return [
                'id' => $item['id'],
                'content' => $item['content'],
                'date' => $item['date_string'],
                'icon' => $item['icon'] ?? 'dot',
                'color' => $item['color'] ?? 'gray'
            ];
        })->values()->all();

        // Reverse timeline to show newest first - Wait, sortByDesc already puts newest first.
        // Original code was array_reverse($timeline). 
        // We want Newest at Top usually for timelines.
        
        // return response()->json([... 'timeline' => $timeline]);

        return response()->json([
            'candidate' => [
                'id' => $candidate->id,
                'name' => $candidate->first_name . ' ' . $candidate->last_name,
                'email' => $candidate->email,
                'phone' => $candidate->phone,
                'job_title' => $app ? $app->job->title : 'N/A',
                'status' => $app ? $app->status : 'Applied',
                'score' => $app ? $app->score : null,
                'resume_url' => $candidate->resume_path ? asset('storage/' . $candidate->resume_path) : null,
                'answers' => $app ? $app->answers : [], // Screening Answers
                'created_at' => $candidate->created_at->format('M d, Y'),
                'application_id' => $app ? $app->id : null,
            ],
            'timeline' => $timeline,
            'interviews' => $app ? $app->interviews : [],
            'offer' => $app ? $app->offerLetter : null,
            'offerTemplates' => \App\Models\DocumentTemplate::where('is_active', true)->get(), // Return full objects for type grouping
            'salaryStructures' => \App\Models\SalaryStructure::with('components')->where('is_active', true)->get()
        ]);
    }
    public function bulkAction(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:candidates,id',
            'action' => 'required|in:offer,reject,move',
            'data' => 'nullable|array'
        ]);

        $count = 0;
        $activeJobId = $request->input('data.job_id'); // Optional filter context

        try {
            DB::beginTransaction();

            foreach ($validated['ids'] as $candidateId) {
                $candidate = Candidate::find($candidateId);
                
                // Find Relevant Application
                $appQuery = $candidate->applications()->where('status', '!=', 'Rejected');
                if ($activeJobId) {
                    $appQuery->where('job_posting_id', $activeJobId);
                }
                $app = $appQuery->latest('updated_at')->first();

                if (!$app) continue; // Skip if no active app

                if ($validated['action'] === 'offer') {
                    // Create Draft Offer
                    if (!$app->offerLetter) {
                        \App\Models\OfferLetter::create([
                            'job_application_id' => $app->id,
                            'status' => 'Draft',
                            'token' => Str::random(32),
                            'otp' => mt_rand(100000, 999999), // Pre-generate
                            'salary_currency' => 'INR', // Default
                            'created_by' => auth()->id()
                        ]);
                        $app->update(['status' => 'Offer']);
                        $count++;
                    }
                } elseif ($validated['action'] === 'reject') {
                    $app->update([
                        'status' => 'Rejected', 
                        'rejection_reason' => $validated['data']['reason'] ?? 'Bulk Rejection'
                    ]);
                    $count++;
                } elseif ($validated['action'] === 'move') {
                    $app->update(['status' => $validated['data']['status']]);
                    $count++;
                }
            }

            DB::commit();
            
            $this->logger->log('recruitment', 'bulk_action', "Bulk Action: {$validated['action']} on {$count} candidates");

            return back()->with('success', "Processed {$count} candidates.")->setStatusCode(303);

        } catch (\Exception $e) {
            DB::rollBack();
            $this->logger->log('recruitment', 'bulk_action_error', 'Bulk Action Failed', ['error' => $e->getMessage()], 'error');
            return back()->withErrors(['error' => 'Bulk action failed.']);
        }
    }
}
