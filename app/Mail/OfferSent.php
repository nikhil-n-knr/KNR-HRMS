<?php

namespace App\Mail;

use App\Models\OfferLetter;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\Common\TemplateService;

class OfferSent extends Mailable
{
    use Queueable, SerializesModels;

    public $offer;
    public $url;
    public $customBody;
    public $customSubject;

    public function __construct(OfferLetter $offer, $customBody = null, $customSubject = null)
    {
        $this->offer = $offer;
        $this->url = route('portal.offer.show', $offer->token);
        $this->customBody = $customBody;
        $this->customSubject = $customSubject;
    }

    public function build()
    {
        $subject = $this->customSubject ?? 'Job Offer from ' . config('app.name');
        
        $mail = $this->subject($subject)
                     ->view('emails.offers.sent');

        // 1. Manual File
        if ($this->offer->manual_path && Storage::disk('public')->exists($this->offer->manual_path)) {
            $mail->attach(storage_path('app/public/' . $this->offer->manual_path));
        }
        // 2. Template Generated PDF (Stored)
        elseif ($this->offer->document_template_id && $this->offer->template) {
             // Generate (or get existing) and store
             $path = app(TemplateService::class)->generateAndStoreOfferPdf($this->offer);
             
             // Attach the stored file
             $mail->attach(storage_path('app/public/' . $path), [
                 'as' => 'Offer_Letter.pdf',
                 'mime' => 'application/pdf',
             ]);
        }
        
        // 3. Attachments (Template + One-off)
        if ($this->offer->attachments && is_array($this->offer->attachments)) {
            foreach ($this->offer->attachments as $attachment) {
                // $attachment is ['name' => '...', 'path' => '...']
                if (isset($attachment['path']) && Storage::disk('public')->exists($attachment['path'])) {
                    $mail->attach(storage_path('app/public/' . $attachment['path']), [
                        'as' => $attachment['name'] ?? basename($attachment['path'])
                    ]);
                }
            }
        }
        
        return $mail;
    }
}
