<?php

namespace App\Console\Commands\CRM;

use Illuminate\Console\Command;
use App\Models\CRM\Ticket;
use App\Services\CRM\SLAService;

class CheckSLABreaches extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crm:check-sla';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Check all active tickets for SLA breaches';

    /**
     * Execute the console command.
     */
    public function handle(SLAService $slaService)
    {
        $this->info('Starting SLA breach check...');

        $activeTickets = Ticket::whereNotIn('status', ['resolved', 'closed'])->get();
        
        $bar = $this->output->createProgressBar(count($activeTickets));
        $bar->start();

        foreach ($activeTickets as $ticket) {
            $slaService->checkStatus($ticket);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('SLA check completed.');
    }
}
