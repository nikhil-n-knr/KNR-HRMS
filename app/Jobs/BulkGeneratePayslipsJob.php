<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Models\Payroll;
use App\Jobs\GeneratePayslipPdfJob;

class BulkGeneratePayslipsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $payroll;

    /**
     * Create a new job instance.
     */
    public function __construct(Payroll $payroll)
    {
        $this->payroll = $payroll;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $payslips = $this->payroll->payslips; // Loading all might be heavy for 10k, but ok for 100.
        
        foreach ($payslips as $slip) {
             GeneratePayslipPdfJob::dispatch($slip);
        }
    }
}
