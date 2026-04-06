<?php

namespace App\Http\Controllers\Talent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\OfferLetter;
use App\Models\Candidate;
use Inertia\Inertia;
use Illuminate\Support\Facades\Mail;
use App\Mail\OfferSent;

class OfferApprovalController extends Controller
{
    // View for Approvers (Protected by Token or Auth)
    public function show(Request $request, Candidate $candidate)
    {
        // Find offers for this candidate
        // Prioritize: Pending_Approval > Approved > Draft > Rejected
        $offer = OfferLetter::whereHas('jobApplication', function($q) use ($candidate) {
            $q->where('candidate_id', $candidate->id);
        })
        ->orderByRaw("FIELD(status, 'Pending_Approval', 'Approved', 'Draft', 'Rejected', 'Sent', 'Accepted') ASC")
        ->latest('updated_at')
        ->firstOrFail();

        // Check token if provided, or Auth
        if ($request->token && $offer->approval_token !== $request->token) {
            abort(403, 'Invalid approval token.');
        }

        $offer->load([
            'jobApplication.candidate', 
            'jobApplication.interviews.feedback', // Now works with hasOne
            'jobApplication.interviews.interviewer',
            // 'jobApplication.interviews.round', // REMOVED: round is a column, not a relation
            'salaryStructure',
            'documents',
            'template'
        ]);

        return Inertia::render('Talent/Offers/Approval', [
            'offer' => $offer,
            'candidate' => $offer->jobApplication->candidate,
            // Filter out cancelled interviews to show only relevant history
            'interviews' => $offer->jobApplication->interviews
                ? $offer->jobApplication->interviews->where('status', '!=', 'Cancelled')->values()
                : [],
            'documents' => $offer->documents ?? [],
            'approval_data' => $offer->approval_data,
            'pdf_preview_url' => route('talent.offers.preview-pdf', $offer->id), // Keep PDF preview ID-based for now or update similarly if needed
            'is_approver' => true
        ]);
    }

    public function previewPdf(OfferLetter $offer)
    {
         // On-the-fly PDF generation for Approver
         $offer->load('jobApplication.candidate', 'template');
        // Use TemplateService to generate/cache the PDF
        // This ensures the preview matches exactly what will be sent
        // And satisfies "generate in one place" requirement
        try {
            $path = app(\App\Services\Common\TemplateService::class)->generateAndStoreOfferPdf($offer);
            
            return response()->file(storage_path('app/public/' . $path));
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to generate PDF: ' . $e->getMessage());
        }
    }

    public function approve(Request $request, OfferLetter $offer)
    {
        // Simple logic: If ANY approver approves, it is Approved? Or All?
        // For MVP: Single approval moves it to Approved.
        
        $offer->fill([
            'approval_status' => 'Approved',
            'status' => 'Approved' 
        ])->save();

        // Notify Creator?
        // ...

        return back()->with('success', 'Offer Approved successfully.');
    }

    public function reject(Request $request, OfferLetter $offer)
    {
        $request->validate(['reason' => 'required|string']);

        $offer->update([
            'approval_status' => 'Rejected',
            'status' => 'Rejected', 
            // Store reason in notes or simple log?
        ]);

        return back()->with('success', 'Offer Rejected.');
    }
}
