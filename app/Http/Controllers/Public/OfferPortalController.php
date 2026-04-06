<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\OfferLetter;
use App\Models\DocumentRequest;
use Inertia\Inertia;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Session;
use App\Services\Infrastructure\LoggerService;

class OfferPortalController extends Controller
{
    protected $logger;

    public function __construct(LoggerService $logger)
    {
        $this->logger = $logger;
    }

    // Helper to resolve offer by Token or Candidate UUID
    private function resolveOffer($identifier)
    {
        if (Str::isUuid($identifier)) {
            // Assume Candidate ID
            // Find Candidate -> Latest Active Job Application -> Latest Offer
            // We need to fetch offer regardless of status if it's for preview
            $offer = OfferLetter::whereHas('jobApplication', function($q) use ($identifier) {
                $q->where('candidate_id', $identifier);
            })->latest('created_at')->first();
            
            if ($offer) return $offer;
        }
        
        return OfferLetter::where('token', $identifier)->firstOrFail();
    }

    // 1. Show Login Page
    public function login(Request $request, $token)
    {
        $offer = $this->resolveOffer($token);
        $offer->load('jobApplication.candidate');

        // Check if already authenticated
        if (Session::get('offer_session_' . $offer->id)) {
             return redirect()->route('portal.offer.show', $token);
        }

        return Inertia::render('Public/Offer/Login', [
            'token' => $token,
            'candidate_email_mask' => Str::mask($offer->jobApplication->candidate->email, '*', 3, -3),
            'candidate_name' => $offer->jobApplication->candidate->first_name
        ]);
    }

    // 2. Send OTP (Deprecated: We use static PIN now)
    public function sendOtp(Request $request, $token)
    {
        // NO-OP or Legacy support if needed. 
        // We now expect user to enter the PIN shared by admin.
        return response()->json(['message' => 'Please enter the PIN provided in your offer email.'], 200);
    }

    // 3. Verify PIN
    public function verifyOtp(Request $request, $token)
    {
        $request->validate(['otp' => 'required|string', 'email' => 'required|email']);
        
        $offer = $this->resolveOffer($token);
        $offer->load('jobApplication.candidate');

        // Verify Email
        if (strtolower($request->email) !== strtolower($offer->jobApplication->candidate->email)) {
            return back()->withErrors(['email' => 'Email does not match our records.']);
        }

        // Verify Static PIN
        // We compare strictly string to string to avoid 00123 == 123 issues if relevant, 
        // though standard equality is usually fine for numeric strings.
        if ((string)$request->otp !== (string)$offer->otp) {
             return back()->withErrors(['otp' => 'Invalid PIN. Please check the code provided.']);
        }

        // Authenticate
        Session::put('offer_session_' . $offer->id, true);
        
        return redirect()->route('portal.offer.show', $token);
    }


    // 4. Show Offer Dashboard
    public function show(Request $request, $token)
    {
        $offer = $this->resolveOffer($token);
        $offer->load(['documents', 'jobApplication.candidate', 'jobApplication.job', 'template']);
        
        // Middleware-like check
        if (!Session::get('offer_session_' . $offer->id)) {
            return redirect()->route('portal.offer.login', $token);
        }
        
        // 1. Check for Withdrawn Status
        if ($offer->status === 'Withdrawn') {
            return Inertia::render('Public/OfferExpired', [
                'message' => 'This offer has been withdrawn by the company.',
                'type' => 'withdrawn'
            ]);
        }

        // 2. Check for Expiry
        if ($offer->expiry_date && $offer->expiry_date->isPast() && $offer->status !== 'Accepted') {
             return Inertia::render('Public/OfferExpired', [
                'message' => 'This offer has expired.',
                'type' => 'expired'
            ]);
        }

        $this->logger->log('recruitment', 'offer_viewed_candidate', 'Candidate Viewed Offer', ['offer_id' => $offer->id]);

        // Render content logic
        $content = '';
        $templateConfig = null;

        // Check for Mandatory Documents that are pending upload
        $hasPendingMandatory = $offer->documents->where('is_mandatory', true)->whereIn('status', ['Pending', 'Rejected'])->isNotEmpty();

        if ($offer->status === 'Pending_Docs' || $hasPendingMandatory) {
             // Locked state
        } else {
             if (!empty($offer->content)) {
                $content = $offer->content;
            } elseif ($offer->template) {
                $content = $offer->template->content;
            }

            // Extract Template Configuration (Header/Footer/Watermark)
            if ($offer->template) {
                $templateConfig = [
                    'header_html' => $offer->template->header_html,
                    'footer_html' => $offer->template->footer_html,
                    'watermark_text' => $offer->template->watermark_text,
                    'header_image' => $offer->template->header_image,
                    'footer_image' => $offer->template->footer_image,
                    'watermark_image' => $offer->template->watermark_image,
                    'layout_config' => $offer->template->layout_config 
                ];
            }
        }

        // Build Candidate Journey Timeline
        $timeline = [];
        $app = $offer->jobApplication;
        if ($app) {
            $timeline[] = ['title' => 'Sample Applied', 'date' => $app->created_at->format('M d, Y'), 'icon' => 'check', 'color' => 'green'];
            if ($app->interview_date) {
                 $timeline[] = ['title' => 'Interview', 'date' => \Carbon\Carbon::parse($app->interview_date)->format('M d, Y'), 'icon' => 'check', 'color' => 'green'];
            }
            $timeline[] = ['title' => 'Offer Received', 'date' => $offer->created_at->format('M d, Y'), 'icon' => 'star', 'color' => 'indigo'];
            
            if ($offer->status === 'Accepted') {
                 $timeline[] = ['title' => 'Offer Accepted', 'date' => $offer->updated_at->format('M d, Y'), 'icon' => 'check', 'color' => 'green'];
            }
        }

        return Inertia::render('Public/Offer/Show', [
            'offer' => $offer,
            'content' => $content,
            'candidate' => $offer->jobApplication->candidate,
            'timeline' => $timeline,
            'hasPendingMandatory' => $hasPendingMandatory,
        ]);
    }

    // 5. Action (Accept/Reject)
    public function update(Request $request, $token)
    {
         $offer = $this->resolveOffer($token);
         if (!Session::get('offer_session_' . $offer->id)) abort(403);

         $request->validate(['action' => 'required|in:accept,reject', 'reason' => 'required_if:action,reject']);

         if ($request->action === 'accept') {
             $offer->update([
                 'status' => 'Accepted',
                 'accepted_at' => now(),
                 'accepted_ip' => $request->ip(),
                 'candidate_preferences' => $request->input('preferences'),
                 'signature_image' => $request->input('signature')
             ]);

             // Sync Status
             $offer->jobApplication->update(['status' => 'Hired']);
             // $offer->jobApplication->candidate->update(['status' => 'Hired']); // Removed: Candidate table has no status column
             
             // Regenerate PDF to include the signature
             try {
                // Refresh model to get the saved signature logic via getDataAttribute if needed
                $offer->refresh(); 
                app(\App\Services\Common\TemplateService::class)->generateAndStoreOfferPdf($offer);
             } catch (\Exception $e) {
                 // Log error but don't fail the request
                 $this->logger->log('recruitment', 'pdf_regen_error', 'Failed to regenerate signed PDF', ['error' => $e->getMessage()]);
             }

         } else {
             $offer->update([
                 'status' => 'Rejected'
             ]);

             // Sync Status
             $offer->jobApplication->update(['status' => 'Rejected']);
             $offer->jobApplication->candidate->update([
                 'status' => 'Rejected',
                 'rejection_reason' => $request->input('reason')
             ]);
         }
         
         return back()->with('success', 'Response recorded.');
    }

    public function uploadDocument($token, DocumentRequest $documentRequest, Request $request)
    {
        $offer = $this->resolveOffer($token);
        
        if (!Session::get('offer_session_' . $offer->id)) abort(403);
        if ($documentRequest->offer_letter_id !== $offer->id) abort(403);

        $request->validate([
            'file' => 'required|file|max:5120|mimes:pdf,jpg,jpeg,png'
        ]);

        $path = $request->file('file')->store('offer-documents/' . $offer->id, 'public');

        $documentRequest->update([
            'file_path' => $path,
            'mime_type' => $request->file('file')->getMimeType(),
            'file_size' => $request->file('file')->getSize(),
            'status' => 'Submitted',
            'rejection_reason' => null
        ]);

        $this->logger->log('recruitment', 'offer_document_uploaded', 'Candidate Uploaded Document', ['offer_id' => $offer->id, 'doc_type' => $documentRequest->name]);

        return back()->with('success', 'Document uploaded successfully.');
    }

    public function download($token)
    {
        $offer = $this->resolveOffer($token);
        if (!Session::get('offer_session_' . $offer->id)) abort(403);

        if ($offer->manual_path) {
            return response()->download(storage_path('app/public/' . $offer->manual_path));
        }

        if (empty($offer->content)) {
             return back()->with('error', 'No content available to download.');
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($offer->content);
        $pdf->setPaper('a4', 'portrait');
        return $pdf->download('Offer_Letter.pdf');
    }
}
