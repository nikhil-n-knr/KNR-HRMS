<?php

namespace App\Jobs\CRM;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ArchiveOldEmailsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $cutoffDate = now()->subDays(90);

        // For the prototype, we simply mark them as archived. 
        // In a full implementation, we would move them to an S3-backed archive or a separate table.
        \App\Models\CRM\EmailMessage::where('sent_at', '<', $cutoffDate)
            ->where('status', '!=', 'archived')
            ->chunk(100, function ($messages) {
                foreach ($messages as $message) {
                    // Logic to move to S3/Archive table would go here
                    $message->update(['status' => 'archived']);
                }
            });

        \Illuminate\Support\Facades\Log::info("Archived emails older than {$cutoffDate->format('Y-m-d')}");
    }
}
