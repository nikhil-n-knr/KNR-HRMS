<?php

namespace App\Jobs;

use App\Models\VisitorPass;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateBadgeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $visitorPass;

    /**
     * Create a new job instance.
     */
    public function __construct(VisitorPass $visitorPass)
    {
        $this->visitorPass = $visitorPass;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Spooler simulation: Here we would communicate with the print server or generate the PDF mapping
        // We update the badge_printed_at timestamp
        
        $this->visitorPass->update([
            'badge_printed_at' => now()
        ]);

        Log::info("Badge generated and spooled for Pass ID: {$this->visitorPass->id}");
    }
}
