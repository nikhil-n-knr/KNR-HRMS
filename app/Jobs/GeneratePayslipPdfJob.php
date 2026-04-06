<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Models\Payslip;
use App\Models\SystemSetting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class GeneratePayslipPdfJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $payslip;

    /**
     * Create a new job instance.
     */
    public function __construct(Payslip $payslip)
    {
        $this->payslip = $payslip;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // 1. Fetch Configs
        $configs = [
            'header' => SystemSetting::get('payslip_header'),
            'footer' => SystemSetting::get('payslip_footer'),
            'watermark' => SystemSetting::get('payslip_watermark'),
        ];

        // 2. Load View
        $this->payslip->load(['employee.user', 'employee.department', 'payroll']);
        
        $pdf = Pdf::loadView('pdf.payslip', [
            'payslip' => $this->payslip,
            'payroll' => $this->payslip->payroll,
            'configs' => $configs
        ]);

        // 3. Save to Storage
        $path = 'payslips/' . $this->payslip->payroll_id . '/' . $this->payslip->id . '.pdf';
        Storage::disk('public')->put($path, $pdf->output());

        // 4. Update Path
        $this->payslip->update(['pdf_path' => $path]);
    }
}
