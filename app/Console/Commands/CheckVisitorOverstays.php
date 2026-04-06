<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\VisitorPass;
use Carbon\Carbon;

class CheckVisitorOverstays extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'visitor:check-overstays';
    protected $description = 'Flags visitor passes that have exceeded their expected duration';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for overstayed visitors...');

        $now = Carbon::now();
        
        $activePasses = VisitorPass::with('visitor')
            ->whereNotNull('check_in_at')
            ->whereNull('check_out_at')
            ->where('status', '!=', 'Overstayed')
            ->get();

        $count = 0;
        foreach ($activePasses as $pass) {
            $checkIn = Carbon::parse($pass->check_in_at);
            $expectedMinutes = $pass->expected_duration ?? 60; // Default 60 mins
            
            if ($checkIn->addMinutes($expectedMinutes)->isPast()) {
                $pass->update(['status' => 'Overstayed']);
                $this->warn("Visitor pass {$pass->pass_code} ({$pass->visitor->name}) has been flagged as Overstayed.");
                $count++;
                
                // Optional: Notify security or host
                // Notification::send($pass->visitor->host, new VisitorOverstayNotification($pass));
            }
        }

        $this->info("Scan complete. {$count} visitors flagged.");
    }
}
