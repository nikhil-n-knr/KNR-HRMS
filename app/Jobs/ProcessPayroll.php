<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessPayroll implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    protected $month;
    protected $year;
    protected $payrollId;
    protected $employeeId;

    public function __construct(int $payrollId, ?int $employeeId = null)
    {
        $this->payrollId = $payrollId;
        $this->employeeId = $employeeId;
    }

    public function handle(\App\Services\Payroll\PayrollProcessor $processor): void
    {
        try {
            $payroll = \App\Models\Payroll::find($this->payrollId);
            if (!$payroll) return;

            $processor->process($payroll, $this->employeeId);
            
        } catch (\Exception $e) {
            \Log::error('Payroll Job Failed: ' . $e->getMessage());
            // Optionally update a status to 'Failed' 
            if (isset($payroll)) {
                $payroll->update(['status' => 'Failed']); // Or keep as Draft but log error
            }
        }
    }
}
