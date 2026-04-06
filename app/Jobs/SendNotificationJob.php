<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\Communication\NotificationService;

class SendNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $channel;
    protected $recipient;
    protected $subject;
    protected $content;
    protected $metadata;

    /**
     * Create a new job instance.
     */
    public function __construct($channel, $recipient, $subject, $content, $metadata = [])
    {
        $this->channel = $channel;
        $this->recipient = $recipient;
        $this->subject = $subject;
        $this->content = $content;
        $this->metadata = $metadata;
    }

    /**
     * Execute the job.
     */
    public function handle(NotificationService $service): void
    {
        $service->send(
            $this->channel,
            $this->recipient,
            $this->subject,
            $this->content,
            $this->metadata
        );
    }
}
