<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Payslip;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class EmployeePayslipController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $payslips = Payslip::select('payslips.*')
            ->join('payrolls', 'payslips.payroll_id', '=', 'payrolls.id')
            ->whereHas('employee', function($q) {
                $q->where('user_id', auth()->id());
            })
            ->whereIn('payrolls.status', ['Published', 'Paid'])
            ->with('payroll')
            ->orderBy('payrolls.year', 'desc')
            ->orderBy('payrolls.month', 'desc')
            ->paginate(9)
            ->through(function ($slip) {
                return [
                    'id' => $slip->id,
                    'payslip_number' => $slip->payslip_number,
                    'net_pay' => $slip->net_pay,
                    'month_name' => date('F', mktime(0, 0, 0, $slip->payroll->month, 10)),
                    'year' => $slip->payroll->year,
                    'generated_at' => $slip->created_at->format('d M Y'),
                    'download_url' => route('employee.payslips.download', $slip->id),
                    'status' => 'Paid',
                ];
            });

        return \Inertia\Inertia::render('Employee/Payslips/Index', [
            'payslips' => $payslips
        ]);
    }

    /**
     * Download Payslip PDF
     */
    public function download(Payslip $payslip)
    {
        // 1. Authorization: Ensure current user owns this payslip
        if ($payslip->employee?->user_id !== auth()->id()) {
            return back()->with('error', 'Unauthorized access to this payslip.');
        }

        // 2. Check Status: Must be Published
        // We use 'Published' as the standard public status now.
        // Old statuses might be 'Emailed' or 'Generated'.
        // Let's allow if status is 'Published', 'Paid', 'Emailed', or 'Generated' IF the Payroll is Published.
        // The safest check is: Is the PARENT Payroll published?
        if (!$payslip->payroll || !in_array($payslip->payroll->status, ['Published', 'Paid'])) {
             // For strict compliance. If user wants lenient, we can skip.
             // User prompt: "Only show records where status == PUBLISHED"
             return back()->with('error', 'Payslip is not yet published.');
        }

        // 3. Serve File
        // If file exists on disk
        if ($payslip->file_path && Storage::disk('public')->exists($payslip->file_path)) {
            return Storage::disk('public')->download($payslip->file_path, 'Payslip-' . $payslip->month . '-' . $payslip->year . '.pdf');
        }

        // 4. Fallback: Generate on fly
        $configs = [
            'header' => \App\Models\SystemSetting::get('payslip_header'),
            'footer' => \App\Models\SystemSetting::get('payslip_footer'),
            'watermark' => \App\Models\SystemSetting::get('payslip_watermark'),
        ];
        
        $payslip->load(['employee.user', 'employee.department', 'payroll']);
        
        $pdf = Pdf::loadView('pdf.payslip', [
            'payslip' => $payslip,
            'payroll' => $payslip->payroll,
            'configs' => $configs
        ]);
        
        return $pdf->download('Payslip_' . $payslip->payslip_number . '.pdf');
    }
}
