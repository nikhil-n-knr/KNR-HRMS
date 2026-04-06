<?php

namespace App\Jobs;

use App\Models\Payroll;
use App\Models\Payslip;
use App\Mail\PayslipEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class DispatchPayslipEmails implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $payroll;

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
        // Chunking used if dataset is huge, but here let's iterate.
        // We only send to employees who have an email and a 'Paid' payslip.
        
        $payslips = $this->payroll->payslips()
            ->where('status', 'Paid')
            ->with('employee.user')
            ->get();
            
        Log::info("Starting Bulk Email Dispatch for Payroll: " . $this->payroll->batch_name . " Count: " . $payslips->count());

        foreach ($payslips as $slip) {
            try {
                if ($slip->employee->user->email) {
                    Mail::to($slip->employee->user)->send(new PayslipEmail($slip));
                    Log::info("Sent Payslip to: " . $slip->employee->user->email);
                } else {
                     Log::warning("Skipped Payslip (No Email): " . $slip->employee->employee_code);
                }
            } catch (\Exception $e) {
                Log::error("Failed to send Payslip ID " . $slip->id . ": " . $e->getMessage());
                // Continue to next
            }
        }
        
        Log::info("Bulk Email Dispatch Completed.");
    }
}
