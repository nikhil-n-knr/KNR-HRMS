<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\HR\PulseService;
use App\Models\Employee;

class CalculatePulse extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pulse:aggregate {month? : Format YYYY-MM}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Aggregate employee performance metrics for a given month';

    /**
     * Execute the console command.
     */
    public function handle(PulseService $pulseService)
    {
        $month = $this->argument('month') ?? now()->format('Y-m');
        $this->info("Calculating Pulse Metrics for: $month");

        $employees = Employee::all();
        $bar = $this->output->createProgressBar($employees->count());

        foreach ($employees as $employee) {
            $result = $pulseService->aggregateForEmployee($employee->id, $month);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Pulse calculation complete.");
    }
}
