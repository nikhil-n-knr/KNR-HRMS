<?php

namespace App\Jobs\CRM;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class FetchEmailsJob implements ShouldQueue
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
    public function handle(\App\Services\CRM\EmailSyncService $syncService): void
    {
        \App\Models\CRM\EmailAccount::where('is_active', true)->chunk(50, function ($accounts) use ($syncService) {
            foreach ($accounts as $account) {
                $syncService->syncAccount($account);
            }
        });
    }
}
