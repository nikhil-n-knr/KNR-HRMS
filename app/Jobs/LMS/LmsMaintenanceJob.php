<?php

namespace App\Jobs\LMS;

use App\Models\LMS\LmsCourseProgress;
use App\Models\LMS\LmsEnrollment;
use App\Services\LMS\ProgressAggregationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * LMS Background Sync & Maintenance
 * 
 * Periodically recalculates aggregate progress for all active enrollments.
 * Ensures the 'completion_pct' and certificate eligibility are accurate
 * even if real-time heartbeats were missed.
 */
class LmsMaintenanceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(ProgressAggregationService $aggregator)
    {
        // Fetch recently active enrollments (last 1 hour)
        $enrollments = LmsEnrollment::active()
            ->whereHas('courseProgress', function($q) {
                $q->where('updated_at', '>=', now()->subHour());
            })
            ->get();

        foreach ($enrollments as $enrollment) {
            $aggregator->aggregateCourseProgress($enrollment);
        }
    }
}
