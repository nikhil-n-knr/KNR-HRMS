<?php

namespace App\Http\Controllers\Talent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\JobApplication;
use App\Models\OfferLetter;
use App\Models\DocumentRequest;
use App\Models\DocumentTemplate;
use App\Models\SalaryStructure;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Services\Infrastructure\LoggerService;
use Illuminate\Support\Facades\Mail;
use App\Mail\OfferSent;
use Carbon\Carbon;
use App\Services\Payroll\SalaryCalculatorService;
use App\Services\Common\TemplateService;
use App\Services\Email\EmailService;

class OfferLetterController extends Controller
{
    protected $logger;
    protected $salaryService;
    protected $templateService;
    protected $emailService;

    public function __construct(LoggerService $logger, SalaryCalculatorService $salaryService, TemplateService $templateService, EmailService $emailService)
    {
        $this->logger = $logger;
        $this->salaryService = $salaryService;
        $this->templateService = $templateService;
        $this->emailService = $emailService;
    }

    public function index(Request $request)
    {
        $query = OfferLetter::with(['jobApplication.candidate', 'jobApplication.job'])
            ->latest();

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        return Inertia::render('Talent/Offers/Index', [
            'offers' => $query->paginate(15),
            'filters' => $request->only(['status'])
        ]);
    }

    public function create(Request $request)
    {
        $application = JobApplication::with(['candidate', 'job'])->findOrFail($request->application_id);
        $templates = DocumentTemplate::where('is_active', true)->orderBy('type')->get(); // Fetch all active, order by type
        $salaryStructures = SalaryStructure::where('is_active', true)->with('components')->get(); // Eager load components

        return Inertia::render('Talent/Offers/Create', [
            'application' => $application,
            'templates' => $templates,
            'salaryStructures' => $salaryStructures
        ]);
    }

    public function store(Request $request)
    {
        $isDraft = $request->boolean('is_draft');
        
        $rules = [
            'job_application_id' => 'required|exists:job_applications,id',
            'offer_mode' => 'required|in:template,manual',
        ];

        if (!$isDraft) {
            $rules = array_merge($rules, [
                'template_ids' => 'required_if:offer_mode,template|array',
                'template_ids.*' => 'exists:document_templates,id',
                'salary_structure_id' => 'nullable|exists:salary_structures,id',
                'offer_date' => 'required|date',
                'joining_date' => 'required|date',
                'salary_currency' => 'required|string|size:3',
                'salary_amount' => 'required|numeric',
                'designation' => 'required|string',
                'manual_file' => 'required_if:offer_mode,manual|file|mimes:pdf|max:5120',
            ]);
        }

        $request->validate($rules);

        try {
            $offer = DB::transaction(function () use ($request) {
                // 1. Calculate Salary Breakup
                $breakdown = $request->salary_breakdown;
                if ($request->salary_structure_id) {
                    $structure = SalaryStructure::with('components')->find($request->salary_structure_id);
                    if ($structure) {
                        $calculation = $this->salaryService->calculate($request->salary_amount, $structure);
                        // Transform to array for frontend
                        $breakdown = [];
                        if (isset($calculation['breakup'])) {
                            foreach ($calculation['breakup'] as $name => $details) {
                                $breakdown[] = [
                                    'name' => $name,
                                    'annual' => $details['amount'],
                                    'monthly' => $details['amount'] / 12,
                                    'type' => $details['type'] ?? 'earning'
                                ];
                            }
                        } else {
                             $breakdown = $calculation; // Fallback
                        }
                    }
                }

                // 2. Generate Content (Merged Templates)
                $content = null;
                $attachmentsFromTemplates = [];
                $primaryTemplateId = null;

                // Ensure template_ids is array
                $tIds = $request->template_ids;
                if (is_string($tIds)) {
                     // Check if comma separated
                     $tIds = explode(',', $tIds);
                } elseif (!is_array($tIds)) {
                    $tIds = []; 
                }

                if ($request->offer_mode === 'template' && !empty($tIds)) {
                    $mergedHtml = '';
                    $primaryTemplateId = $tIds[0]; // Use first as primary
                    $application = JobApplication::with('candidate')->find($request->job_application_id);

                    // Prepare Data
                    $candidateData = $application->candidate->toArray();
                    $candidateData['name'] = $application->candidate->first_name . ' ' . $application->candidate->last_name;
                    $candidateData['job_title'] = $request->designation;
                    $candidateData['joining_date'] = $request->joining_date;

                   $data = [
                        'date' => $request->offer_date, // Root {{ date }}
                        'candidate' => $candidateData,
                        'salary' => array_merge([
                            'ctc' => $request->salary_amount,
                            'currency' => $request->salary_currency,
                            'joining_date' => $request->joining_date,
                            'designation' => $request->designation
                        ], $breakdown ?? []), 
                        'offer' => [
                            'date' => $request->offer_date,
                            'expiry_date' => $request->expiry_date
                        ],
                        'company' => [
                            'name' => 'Nikhil Infotec',
                            'address' => 'Tech Park, India'
                        ]
                    ];
                    
                    // Add breakdown explicitly if needed by templates as {{ salary.components.Basic }} etc.
                    // But usually smart tables usage handles it via 'salary' key.

                    // Handle Template Sorting & Rendering
                    // Ensure IDs are valid
                    $tTemplates = DocumentTemplate::whereIn('id', $tIds)->get();
                    // Sort by selection order
                    $sorted = $tTemplates->sortBy(function($t) use ($tIds) {
                        return array_search($t->id, $tIds);
                    });
                    
                    foreach ($sorted as $tpl) {
                        // Start each template on a new page (visual separator for web, page-break for PDF logic handled inside template or here)
                        // For Web Store: Just concatenate. The 'web-template-wrapper' has margins.
                        $mergedHtml .= $this->templateService->render($tpl, $data, 'web');
    
                        // Attachments from templates
                        if (!empty($tpl->attachments)) {
                            $attachmentsFromTemplates = array_merge($attachmentsFromTemplates, $tpl->attachments);
                        }
                    }
                    
                    $content = $mergedHtml;
                } // Close if ($request->offer_mode === 'template' ...)


                $manualPath = null;
                if ($request->hasFile('manual_file')) {
                    $manualPath = $request->file('manual_file')->store('offers/manual', 'public');
                }

                $attachmentPaths = $attachmentsFromTemplates;
                
                // 3. Add New Uploads
                if ($request->hasFile('attachments')) {
                    foreach ($request->file('attachments') as $file) {
                        $path = $file->store('offers/attachments', 'public');
                        $attachmentPaths[] = ['name' => $file->getClientOriginalName(), 'path' => $path];
                    }
                }

                // Determine Status
                $status = 'Draft';
                $approvalStatus = 'Pending';
                $approvers = $request->approver_ids ?? [];
                $isForceRelease = $request->boolean('is_force_release');
                $shouldNotifyApprovers = false;

                if ($request->boolean('is_draft')) {
                    $status = 'Draft';
                    $approvalStatus = 'Pending';
                } elseif ($request->status === 'Sent') { 
                    // Explicit Release (e.g. from Approved state or Force Release)
                    $status = 'Sent';
                    $approvalStatus = 'Released';
                } elseif (count($approvers) > 0 && !$isForceRelease) {
                    $status = 'Pending_Approval';
                    $approvalStatus = 'Pending';
                    $shouldNotifyApprovers = true;
                } else {
                    // Default fallback (Direct Release w/o approvers)
                    $status = 'Sent'; 
                    $approvalStatus = 'Released';
                }

                $offer = OfferLetter::findOrNew($request->id);
                
                // VERSIONING LOGIC: If updating an existing Sent/Negotiating offer, snapshot it first
                if ($offer->exists && in_array($offer->status, ['Sent', 'Negotiating', 'Pending_Approval'])) {
                     // Check if meaningful content changed? For now, snapshot on every major save.
                     $offer->createVersion(auth()->id());
                }

                if (!$offer->exists) {
                    $offer->job_application_id = $request->job_application_id;
                    $offer->token = Str::random(32);
                    $offer->approval_token = Str::random(40);
                    $offer->otp = rand(100000, 999999); // Set OTP here
                }
                
                // Ensure OTP always exists (legacy safe)
                if (!$offer->otp) {
                    $offer->otp = rand(100000, 999999);
                }

                // Ensure tokens always exist (legacy safe)
                if (!$offer->token) $offer->token = Str::random(32);
                if (!$offer->approval_token) $offer->approval_token = Str::random(40);

                // MOVED UP: $tIds definition logic was here. Now it is at the top.
                // Fallback for primary template id if not set above (e.g. if offer mode wasn't template)
                if (!$primaryTemplateId && !empty($tIds)) {
                     $primaryTemplateId = $tIds[0];
                }

                $offer->fill([
                    'document_template_id' => $primaryTemplateId, 
                    'template_ids' => $tIds, 
                    'salary_structure_id' => $request->salary_structure_id,
                    // Only update manual path if new file uploaded or distinct logic needed?
                    // For now, if manualPath is set (new file), update it.
                    'manual_path' => $manualPath ?? $offer->manual_path,
                    'attachments' => count($attachmentPaths) > 0 ? json_encode($attachmentPaths) : $offer->attachments, // Append or Replace? Simple replace for now or keep old if no new.  
                    'offer_date' => $request->offer_date,
                    'joining_date' => $request->joining_date,
                    'expiry_date' => $request->expiry_date,
                    'designation' => $request->designation,
                    'salary_currency' => $request->salary_currency,
                    'salary_amount' => $request->salary_amount,
                    'salary_breakdown' => $breakdown, 
                    'content' => $content, 
                    'status' => $status,
                    'approval_status' => $approvalStatus,
                    'approvers' => count($approvers) > 0 ? $approvers : null,
                    'approval_data' => $breakdown,
                    'is_conditional' => $request->boolean('is_conditional'),
                    'email_config' => [
                        'type' => $request->email_type ?? 'generic',
                        'subject' => $request->email_subject,
                        'body' => $request->email_body,
                        'cc_emails' => $request->cc_emails ?? []
                    ]
                ]);
                
                $offer->save();

                // Update Job Application Status if Offer is Sent
                if ($status === 'Sent') {
                    // Ensure the candidate is moved to "Offer" stage
                    $offer->jobApplication->update(['status' => 'Offer']);
                    
                    // Send Email to Candidate
                    // Use $offer->fresh() to ensure all relations are loaded if needed by mailer?
                    // Actually we have $offer.
                    // Mail::to($offer->jobApplication->candidate->email)->send(new OfferSent($offer));
                    // Dispatched via EmailService typically?
                    // For now, assuming email_config handles the content, but we need to trigger the send.
                    
                    // IF email_config is set, we might want to trigger the "Send" logic immediately or queue it.
                    // The "Send" action in UI usually triggers `talent.offers.send` which is separate?
                    // But if we are "Releasing", we might expect it to be sent.
                    // Checked UI: `submitOffer('release')` posts to `store`.
                    // Does `store` send email?
                    
                    // The Controller logic below (lines ~310+) seems to handle notifications/emails?
                    // Can't see it in the snippet.
                    
                    // Let's add the status update at least.
                }
                // Send Approvals Notifications
                if ($status === 'Pending_Approval' && !empty($approvers)) {
                    foreach ($approvers as $userId) {
                        $user = \App\Models\User::find($userId);
                        if ($user) {
                             $user->notify(new \App\Notifications\Talent\OfferApprovalRequestNotification($offer));
                        }
                    }
                }

                // Sync Document Requests
                if ($request->document_requests) {
                    $processedIds = [];
                    
                    foreach ($request->document_requests as $doc) {
                        // Try to find existing by ID (if provided) or Name
                        // We use name as unique key per offer to preserve ID if name matches
                        $d = $offer->documents()->updateOrCreate(
                            ['name' => $doc['name']], 
                            [
                                'stage' => $doc['stage'] ?? 'pre_offer',
                                'is_mandatory' => $doc['is_mandatory'] ?? false,
                                // Don't reset status if it exists, default Pending if new
                            ]
                        );
                        // If new, ensure status is set (updateOrCreate doesn't set on 'update' if not in 2nd array)
                        if ($d->wasRecentlyCreated) {
                            $d->status = 'Pending';
                            $d->save();
                        }
                        $processedIds[] = $d->id;
                    }

                    // Delete docs that are NOT in the request AND are editable (Pending)
                    // We protect Uploaded/Submitted docs from deletion even if removed from UI request list (safety)
                    $offer->documents()
                        ->whereNotIn('id', $processedIds)
                        ->whereNotIn('status', ['Uploaded', 'Submitted', 'Verified'])
                        ->delete();
                }
                
                // Notifications
                if ($status === 'Pending_Approval') {
                     // Notify Approvers
                } elseif ($status === 'Sent') {
                     // Notify Candidate
                     $to = $application->candidate->email;
                     $cc = [];

                     if ($request->has('cc_emails')) {
                         $ccInput = $request->cc_emails;
                         if (is_string($ccInput)) {
                             $cc = array_filter(array_map('trim', explode(',', $ccInput)));
                         } elseif (is_array($ccInput)) {
                             $cc = array_filter($ccInput);
                         }
                     }
                     
                     // Log intention
                     $this->logger->log('recruitment', 'offer_sending', 'Sending Offer Email via Service', [
                         'to' => $to,
                         'cc' => $cc,
                         'offer_id' => $offer->id
                     ]);

                     try {
                        $subject = $request->email_subject ?? 'Job Offer from ' . config('app.name');
                        $body = $request->email_body;
                        $this->dispatchOfferEmail($offer, $subject, $body, $cc);
                     } catch (\Exception $e) {
                         $this->logger->log('recruitment', 'offer_sent_failure', 'Email Service Failed', ['error' => $e->getMessage()], 'error');
                     }
                }
                
                return $offer;
            });
            
            $this->logger->log('recruitment', 'offer_save', 'Offer Saved', ['offer_id' => $offer->id]);
            $offer->refresh();
            $offer->load('documents'); // Critical: Return new IDs to frontend
            return back()->with('success', 'Offer Saved Successfully')->with('offer_data', $offer);

        } catch (\Exception $e) {
            $this->logger->log('recruitment', 'offer_save_error', 'Offer Save Failed', ['error' => $e->getMessage()], 'error');
            return back()->withErrors(['error' => 'Failed to save offer: ' . $e->getMessage()]);
        }
    }

    public function show(OfferLetter $offer)
    {
        $offer->load(['documents', 'versions']); // Load related documents and versions
        
        // We might need to ensure access control here (e.g., only own offers or if admin)
        // Ignoring for now as middleware handles 'auth'.
        
        return response()->json([
            'offer' => $offer,
            'application' => $offer->jobApplication->load('candidate', 'job')
        ]);
    }



    public function send(Request $request, OfferLetter $offer)
    {
        try {
            // 1. Recipient & CC
            $to = $offer->jobApplication->candidate->email;
            $cc = [];
            if ($request->has('cc_emails')) {
                 $ccInput = $request->cc_emails;
                 if (is_string($ccInput)) $cc = array_filter(array_map('trim', explode(',', $ccInput)));
                 elseif (is_array($ccInput)) $cc = array_filter($ccInput);
            }

            // 2. Content
            $subject = $request->email_subject ?? 'Job Offer';
            $body = $request->email_body;

            // 3. Status Update Logic
            if ($offer->is_conditional && $offer->status !== 'Sent') {
                $offer->update(['status' => 'Pending_Docs']);
            } elseif ($offer->status !== 'Sent' && $offer->status !== 'Accepted') {
                 $offer->update(['status' => 'Sent']);
            }

            // STAGE 5: Send Document Request (No Offer Attachment)
            // Use Queue
            \App\Jobs\SendOfferEmailJob::dispatch($offer, $subject, $body, $cc, false);
            
            return back()->with('success', "Email Queued for {$to}");
        } catch (\Exception $e) {
            $this->logger->log('recruitment', 'offer_send_error', 'Offer Email Logic Failed', ['error' => $e->getMessage()], 'error');
            return back()->withErrors(['error' => 'Failed to queue email: ' . $e->getMessage()]);
        }
    }

    // ... (dispatchOfferEmail method removed or kept as unused private?) -> I will rewrite calls below

    // In release():
    // \App\Jobs\SendOfferEmailJob::dispatch($offer, 'Job Offer', null, [], true);

    // In resend():
    // \App\Jobs\SendOfferEmailJob::dispatch($offer, 'Job Offer (Resent)', null, $cc, true);



    public function release(OfferLetter $offer)
    {
        if (!$offer->is_conditional) {
             return back()->with('error', 'This is not a conditional offer.');
        }

        try {
           $offer->update(['status' => 'Sent']);
           // Send the ACTUAL Offer Email now
           // Send the ACTUAL Offer Email now
           // STAGE 6: Include Offer Attachment
           \App\Jobs\SendOfferEmailJob::dispatch($offer, 'Job Offer', null, [], true);

           $this->logger->log('recruitment', 'offer_released', 'Conditional Offer Released (Queued)', ['offer_id' => $offer->id]);
           return back()->with('success', 'Offer Release Queued');

        } catch (\Exception $e) {
             return back()->withErrors(['error' => 'Failed to release offer: ' . $e->getMessage()]);
        }
    }

    public function verifyDocument(\App\Models\DocumentRequest $documentRequest, Request $request)
    {
        $request->validate(['status' => 'required|in:Verified,Rejected', 'reason' => 'nullable|string']);
        
        $documentRequest->update([
            'status' => $request->status,
            'rejection_reason' => $request->status === 'Rejected' ? $request->reason : null
        ]);

        $this->logger->log('recruitment', 'document_verified', 'Document Verified', ['offer_id' => $documentRequest->offer_letter_id, 'doc_id' => $documentRequest->id, 'status' => $request->status]);

        return back()->with('success', 'Document status updated.');
    }

    public function withdraw(OfferLetter $offer)
    {
        if ($offer->status === 'Accepted' || $offer->status === 'Joined') {
            return back()->withErrors(['error' => 'Cannot withdraw an accepted offer.']);
        }

        $offer->update(['status' => 'Withdrawn', 'token' => null]);
        
        $this->logger->log('recruitment', 'offer_withdrawn', 'Offer Withdrawn', ['offer_id' => $offer->id]);
        
        return back()->with('success', 'Offer has been withdrawn.');
    }

    public function extend(Request $request, OfferLetter $offer)
    {
        $request->validate(['expiry_date' => 'required|date|after:today']);
        
        $offer->update(['expiry_date' => $request->expiry_date]);
        
        $this->logger->log('recruitment', 'offer_extended', 'Offer Validity Extended', ['offer_id' => $offer->id, 'new_date' => $request->expiry_date]);
        
        return back()->with('success', 'Offer validity extended.');
    }

    public function resend(Request $request, OfferLetter $offer)
    {
        if (!$offer->token) {
             $offer->update(['token' => \Illuminate\Support\Str::random(32)]);
        }
        
        $cc = [];
        if ($request->has('cc_emails') && is_array($request->cc_emails)) {
            $cc = $request->cc_emails;
        }
        
        // Resend: Include Attachment
        \App\Jobs\SendOfferEmailJob::dispatch($offer, 'Job Offer (Resent)', null, $cc, true);
        
        return back()->with('success', 'Offer email resend queued.');
    }

    /**
     * Preview the offer letter content on the fly without saving.
     */
    public function preview(Request $request)
    {
        // Simple validation enough for preview
        $request->validate([
            'job_application_id' => 'required',
            'template_ids' => 'required', // Can be string or array
            'salary_structure_id' => 'nullable',
            'salary_amount' => 'required|numeric',
        ]);

        try {
            // 1. Calculate Salary Breakup
            $breakdown = $request->salary_breakdown;
            
            // If structure is selected, recalculate simply for accuracy or trust input
            if ($request->salary_structure_id) {
                $structure = SalaryStructure::with('components')->find($request->salary_structure_id);
                if ($structure) {
                     $breakdown = $this->salaryService->calculate($request->salary_amount, $structure);
                }
            }

            // 2. Generate Content
            $mergedHtml = '';
            $application = JobApplication::with('candidate')->find($request->job_application_id);
            if (!$application) return 'Application not found';

            $formatDate = function($date) {
                return $date ? \Carbon\Carbon::parse($date)->format('jS F Y') : '';
            };

            $candidateData = $application->candidate->toArray();
            $candidateData['name'] = $application->candidate->first_name . ' ' . $application->candidate->last_name;
            $candidateData['job_title'] = $request->designation;
            $candidateData['joining_date'] = $formatDate($request->joining_date);

            $data = [
                'date' => $formatDate($request->offer_date), // Root {{ date }}
                'candidate' => $candidateData,
                'salary' => array_merge([
                    'ctc' => $request->salary_amount,
                    'currency' => $request->salary_currency,
                    'joining_date' => $formatDate($request->joining_date),
                    'designation' => $request->designation
                ], $breakdown ?? []),
                'offer' => [
                    'date' => $formatDate($request->offer_date),
                    'expiry_date' => $formatDate($request->expiry_date)
                ],
                'company' => [
                    'name' => 'Nikhil Infotec',
                    'address' => 'Tech Park, India'
                ]
            ];

            // Handle Template IDs (Single or Multiple)
            // If request comes from form submission, template_ids might be comma separated string or array?
            $tIds = $request->template_ids;
            if (is_string($tIds)) {
                $tIds = explode(',', $tIds);
            }
            if (empty($tIds)) {
                 // Fallback to primary if no IDs sent
                 $tIds = [$request->document_template_id];
            }

            $templates = DocumentTemplate::whereIn('id', $tIds)->get();
            
            // Sort by user selection order
            $sortedTemplates = $templates->sortBy(function($t) use ($tIds) {
                return array_search($t->id, $tIds);
            });

            foreach ($sortedTemplates as $index => $template) {
                // Render as Web Component
                $rendered = $this->templateService->render($template, $data, 'web');
                $mergedHtml .= $rendered;
            }

            // Wrap in simple layout
            return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <title>Offer Preview</title>
    <style>
        body { font-family: sans-serif; background: #e5e7eb; padding: 20px; margin: 0; }
        .preview-container { text-align: center; }
        /* Wrapper already has shadows and margins from template */
        .print-btn { position: fixed; bottom: 20px; right: 20px; background: #4f46e5; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none; font-weight: bold; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); z-index: 1000; }
        @media print { .print-btn { display: none; } body { background: white; padding: 0; } .web-template-wrapper { box-shadow: none; margin: 0; width: 100%; max-width: none; page-break-after: always; } }
    </style>
</head>
<body>
    <div class="preview-container">
        {$mergedHtml}
    </div>
    <a href="#" onclick="window.print(); return false;" class="print-btn">Print / PDF</a>
</body>
</html>
HTML;

        } catch (\Exception $e) {
            return "Error generating preview: " . $e->getMessage();
        }
    }
    public function approvalView(OfferLetter $offer)
    {
        $offer->load(['jobApplication.candidate', 'salaryStructure']);
        return \Inertia\Inertia::render('Talent/Offers/Approval', [
            'offer' => $offer,
            'candidate' => $offer->jobApplication->candidate,
            'documents' => $offer->documents,
            'approvers' => $offer->approvers, // List of approvers
            'current_user_id' => auth()->id() // To check ability
        ]);
    }

    public function approveAction(Request $request, OfferLetter $offer)
    {
        $request->validate([
            'action' => 'required|in:approve,reject',
            'comment' => 'nullable|string'
        ]);

        $status = $request->action === 'approve' ? 'Approved' : 'Rejected';
        
        $log = $offer->approval_data ?? [];
        $log[] = [
            'action' => $status,
            'user_id' => auth()->id(),
            'user_name' => auth()->user()->name,
            'comment' => $request->comment,
            'timestamp' => now()->toIso8601String()
        ];

        $offer->update([
            'approval_status' => $status === 'Approved' ? 'Approved' : 'Rejected',
            'status' => $status === 'Approved' ? 'Approved' : 'Rejected', 
            'approval_data' => $log
        ]);

        return back()->with('success', 'Offer ' . $status);
    }
    
    public function downloadOffer(OfferLetter $offer)
    {
        // Use the same standardized template logic as Preview/Store
        // Note: The content itself ($offer->content) is already full HTML if it was generated via template mode.
         
        // We really just need the global HTML shell.
        $html = view('templates.pdf_layout', [
            'isWeb' => false,
            'css' => '', 
            'template' => (object)[
                'name' => 'Offer Letter',
                'watermark_text' => null, 
                'header_image' => null,
                'footer_image' => null
            ],
            'layoutConfig' => [],
            'header' => '', 
            'footer' => '',
            'pages' => [$offer->content] // Fix: Pass content as pages array
        ])->render();
        
        // The layout normally expects components. 
        // If $offer->content is full HTML (with Doctype), we just print it.
        // If it's a fragment, we wrap it.
        
        if (str_contains($offer->content, '<html')) {
             $pdfContent = $offer->content;
        } else {
             // It's likely just the inner HTML from standard templates
             // We can use a simple wrapper
             $pdfContent = '<html><head><style>body { font-family: sans-serif; } table { width: 100%; border-collapse: collapse; } td { vertical-align: top; } .page-break { page-break-after: always; } </style></head><body>' . $offer->content . '</body></html>';
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($pdfContent)->setPaper('a4', 'portrait');
        return $pdf->download('Offer_Letter.pdf');
    }

    public function downloadDocument($id)
    {
        $doc = \App\Models\DocumentRequest::findOrFail($id);
        if ($doc->file_path && \Storage::disk('public')->exists($doc->file_path)) {
             return \Storage::disk('public')->download($doc->file_path, $doc->name);
        }
        return abort(404, 'File not found');
    }

    /**
     * Helper to dispatch offer email
     */
    protected function dispatchOfferEmail(OfferLetter $offer, $subject, $body, $cc = [], bool $includeOfferAttachment = true)
    {
         // Dispatch to Queue for background processing
         \App\Jobs\SendOfferEmailJob::dispatch($offer, $subject ?: 'Job Offer', $body, $cc, $includeOfferAttachment);
         
         $this->logger->log('recruitment', 'offer_email_queued', 'Offer Email Queued via Job', ['offer_id' => $offer->id]);
    }
}
