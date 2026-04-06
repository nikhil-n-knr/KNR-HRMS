<?php

namespace App\Jobs;

use App\Models\OfferLetter;
use App\Services\Email\EmailService;
use App\Services\Common\TemplateService;
use App\Services\Infrastructure\LoggerService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendOfferEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $offer;
    protected $subject;
    protected $body;
    protected $cc;
    protected $includeAttachment;

    /**
     * Create a new job instance.
     */
    public function __construct(OfferLetter $offer, string $subject, ?string $body, array $cc = [], bool $includeAttachment = true)
    {
        $this->offer = $offer;
        $this->subject = $subject;
        $this->body = $body;
        $this->cc = $cc;
        $this->includeAttachment = $includeAttachment;
    }

    /**
     * Execute the job.
     */
    public function handle(EmailService $emailService, TemplateService $templateService, LoggerService $logger)
    {
        try {
            $offer = $this->offer;
            $to = $offer->jobApplication->candidate->email;
            
            $logger->log('recruitment', 'email_job_start', 'Processing Offer Email Job', ['offer_id' => $offer->id]);

            // Generate Attachments
            $attachments = [];
            $offer->load('template', 'jobApplication.candidate');

            if ($this->includeAttachment) {
                 if ($offer->document_template_id && $offer->template) {
                     $data = $this->prepareOfferData($offer);
                     $html = $templateService->render($offer->template, $data);
                     $pdfContent = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html)->output();
                     
                     $attachments[] = [
                         'filename' => 'Offer_Letter.pdf',
                         'content' => base64_encode($pdfContent),
                     ];
                 } elseif ($offer->manual_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($offer->manual_path)) {
                     $content = \Illuminate\Support\Facades\Storage::disk('public')->get($offer->manual_path);
                     $attachments[] = [
                         'filename' => 'Offer_Letter.pdf', // Standardized name
                         'content' => base64_encode($content)
                     ];
                 }
            }

            // Additional Attachments
            if ($this->includeAttachment && $offer->attachments) {
                 $extras = is_string($offer->attachments) ? json_decode($offer->attachments, true) : $offer->attachments;
                 if (is_array($extras)) {
                     foreach ($extras as $att) {
                         if (isset($att['path']) && \Illuminate\Support\Facades\Storage::disk('public')->exists($att['path'])) {
                              $content = \Illuminate\Support\Facades\Storage::disk('public')->get($att['path']);
                              $attachments[] = [
                                  'filename' => $att['name'] ?? basename($att['path']),
                                  'content' => base64_encode($content)
                              ];
                         }
                     }
                 }
            }

            // Render Email Body
            $url = route('portal.offer.show', $offer->token ?? 'login');
            // We need to support 'view' inside Job. Views are accessible.
            $emailHtml = view('emails.offers.sent', [
                'offer' => $offer, 
                'customBody' => $this->body, 
                'customSubject' => $this->subject,
                'url' => $url,
                'candidate_name' => $offer->jobApplication->candidate->first_name
            ])->render();

            $payload = [
                'to' => $to,
                'cc' => $this->cc,
                'subject' => $this->subject,
                'html' => $emailHtml,
                'attachments' => $attachments,
                'from' => config('mail.from.address'),
                'from_name' => config('mail.from.name')
            ];
            
            // Log payload size/info
            Log::info('Job Email Payload Prepared', ['offer_id' => $offer->id, 'attachments' => count($attachments)]);

            $emailService->send($payload);
            
            $logger->log('recruitment', 'offer_email_sent_async', 'Offer Email Sent via Job', ['offer_id' => $offer->id]);

        } catch (\Exception $e) {
            Log::error('SendOfferEmailJob Failed: ' . $e->getMessage());
            $logger->log('recruitment', 'offer_email_job_failed', 'Job Failed', ['error' => $e->getMessage()], 'error');
            throw $e; // Retry
        }
    }

    private function prepareOfferData(OfferLetter $offer)
    {
        $offer->load('jobApplication.candidate', 'template');
        $candidate = $offer->jobApplication->candidate;
        
        $formatDate = function($date) {
             return $date ? \Carbon\Carbon::parse($date)->format('jS F Y') : '';
        };

        $candidateData = $candidate->toArray();
        $candidateData['name'] = $candidate->first_name . ' ' . $candidate->last_name;
        $candidateData['job_title'] = $offer->designation;
        $candidateData['joining_date'] = $formatDate($offer->joining_date);

        return [
            'date' => $formatDate($offer->offer_date), // Root {{ date }}
            'candidate' => $candidateData,
            'salary' => array_merge([
                'ctc' => $offer->salary_amount,
                'currency' => $offer->salary_currency,
                'joining_date' => $formatDate($offer->joining_date),
                'designation' => $offer->designation
            ], $offer->salary_breakdown ?? []),
            'offer' => [
                'date' => $formatDate($offer->offer_date),
                'expiry_date' => $formatDate($offer->expiry_date)
            ],
            'company' => [
                'name' => config('app.name'),
                'address' => 'Tech Park, India'
            ]
        ];
    }
}
