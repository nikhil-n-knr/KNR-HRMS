<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Attendance\DailyAttendanceProcessor;
use Carbon\Carbon;

class ProcessDailyAttendance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:process-daily {date? : The date to process (YYYY-MM-DD). Defaults to yesterday.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process daily attendance logs (Mark Absent, Apply Policies)';

    protected $processor;

    public function __construct(DailyAttendanceProcessor $processor)
    {
        parent::__construct();
        $this->processor = $processor;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dateInput = $this->argument('date');
        
        if ($dateInput) {
            $date = Carbon::parse($dateInput);
        } else {
            $date = Carbon::yesterday();
        }

        $this->info("Processing attendance for: " . $date->toDateString());

        $this->processor->process($date);

        $this->info("Daily attendance processing completed.");
    }
}
