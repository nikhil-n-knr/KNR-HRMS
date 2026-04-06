<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ComplianceChallan;
use App\Models\User;
use App\Notifications\Compliance\StatutoryPaymentDue;
use Carbon\Carbon;

class SendComplianceReminders extends Command
{
    protected $signature = 'compliance:reminders';
    protected $description = 'Check for pending Statutory Payments and notify HR';

    public function handle()
    {
        $today = Carbon::now();
        
        // Run only on days 10, 13, 14
        if (!in_array($today->day, [10, 13, 14])) {
            return;
        }

        $prevMonth = $today->subMonth()->month;
        $year = $today->subMonth()->year;
        $monthName = Carbon::createFromDate($year, $prevMonth, 1)->format('F Y');

        $types = ['pf', 'esi', 'pt'];
        $admins = User::role(['Admin', 'HR'])->get();

        foreach ($types as $type) {
            $isPaid = ComplianceChallan::where('month', $prevMonth)
                        ->where('year', $year)
                        ->where('type', $type)
                        ->where('status', 'paid')
                        ->exists();

            if (!$isPaid) {
                $this->info("{$type} payment pending for {$monthName}. Sending notifications...");
                foreach ($admins as $admin) {
                    $admin->notify(new StatutoryPaymentDue($monthName, $type));
                }
            }
        }
    }
}
