<?php

namespace App\Jobs;

use App\Models\ComplianceLicence;
use App\Models\User;
use App\Notifications\Compliance\ComplianceLicenceExpiryNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;

class ComplianceLicenceReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        // Check for licences expiring in 30, 15, and 7 days
        $thresholds = [30, 15, 7, 1];
        
        foreach ($thresholds as $days) {
            $expiryDate = Carbon::today()->addDays($days);
            
            $licences = ComplianceLicence::whereDate('expiry_date', $expiryDate)->get();
            
            if ($licences->count() > 0) {
                // Find HR Admins or Compliance Officers
                $admins = User::whereHas('roles', function($q) {
                    $q->whereIn('name', ['Admin', 'HR']);
                })->get();

                foreach ($licences as $licence) {
                    foreach ($admins as $admin) {
                        $admin->notify(new ComplianceLicenceExpiryNotification($licence, $days));
                    }
                }
            }
        }
    }
}
