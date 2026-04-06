<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ArchiveLogsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $days;

    /**
     * Create a new job instance.
     */
    public function __construct(int $days = 30)
    {
        $this->days = $days;
    }

    /**
     * Execute the job.
     */
    public function handle(\App\Services\Infrastructure\LogArchiverService $archiver): void
    {
        $archiver->archive($this->days);
    }
}
