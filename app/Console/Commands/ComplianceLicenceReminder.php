<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ComplianceLicenceReminder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'compliance:check-licences';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for licences expiring in 30 days and notify admin';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $expiring = \App\Models\ComplianceLicence::whereDate('expiry_date', \Carbon\Carbon::now()->addDays(30))->get();

        foreach ($expiring as $licence) {
            \Illuminate\Support\Facades\Log::warning("Statutory Licence Expiring: {$licence->name} for {$licence->state_code} expires on {$licence->expiry_date->format('Y-m-d')}");
            // Send Notification to HR Admin...
        }

        $this->info("Checked " . $expiring->count() . " expiring licences.");
    }
}
