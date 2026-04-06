<?php

namespace App\Jobs\CRM;

use App\Models\CRM\CampaignRecipient;
use App\Models\CRM\MarketingCampaign;
use App\Services\Email\EmailService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendCampaignEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $campaign;
    public $recipient;

    /**
     * Create a new job instance.
     */
    public function __construct(MarketingCampaign $campaign, CampaignRecipient $recipient)
    {
        $this->campaign = $campaign;
        $this->recipient = $recipient;
    }

    /**
     * Execute the job.
     */
    public function handle(EmailService $emailService): void
    {
        try {
            // Update status to processing if not already
            if ($this->recipient->status === 'pending') {
                $this->recipient->update(['status' => 'processing']);
            }

            // Simple template variable replacement
            $content = $this->campaign->content;
            $content = str_replace('{{ name }}', $this->recipient->recipient_name ?? 'there', $content);
            $content = str_replace('{{ email }}', $this->recipient->recipient_email, $content);
            
            // Append Tracking Pixel
            $pixelUrl = route('marketing.track.open', $this->recipient->id);
            $content .= "\n<img src=\"{$pixelUrl}\" width=\"1\" height=\"1\" style=\"display:none;\" alt=\"\" />";

            // Should also add unsubscribe link logic here ideally

            $payload = [
                'from' => config('services.resend.from.name') . ' <' . config('services.resend.from.address') . '>',
                'to' => $this->recipient->recipient_email,
                'subject' => $this->campaign->subject,
                'html' => $content,
            ];

            // Use the EmailService wrapper
            $emailService->send($payload);

            // Mark as sent
            $this->recipient->update([
                'status' => 'sent',
                'sent_at' => now(),
            ]);

        } catch (\Exception $e) {
            Log::error("Failed to send campaign email to {$this->recipient->recipient_email}: " . $e->getMessage());
            
            $this->recipient->update([
                'status' => 'failed',
                'error_message' => substr($e->getMessage(), 0, 500) // Limit length
            ]);
            
            // Re-throw to trigger job failure/retry if configured queue-side
            // keeping it simple for now, maybe don't rethrow to avoid infinite loops if it's a permanent error
            // throw $e; 
        }
    }
}
