<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Payslip;
use Illuminate\Support\Facades\DB;

class PayslipController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user();
        
        // Find Employee record linked to User
        // Assuming User -> hasOne Employee
        $employee = $user->employee; 
        
        if (!$employee) {
            return Inertia::render('Employee/Payslips/Index', [
                'payslips' => [],
                'error' => 'No employee record found linked to your account.'
            ]);
        }

        $payslips = Payslip::where('employee_id', $employee->id)
            ->with(['payroll'])
            ->where('status', '!=', 'Draft') // Show only Generated/Published
            ->orderBy('created_at', 'desc')
            ->paginate(12)
            ->through(function ($slip) {
                return [
                    'id' => $slip->id,
                    'payslip_number' => $slip->payslip_number,
                    'month' => $slip->payroll->month, // integer
                    'year' => $slip->payroll->year,
                    'month_name' => \Carbon\Carbon::createFromDate($slip->payroll->year, $slip->payroll->month, 1)->format('F'),
                    'batch_name' => $slip->payroll->batch_name,
                    'net_pay' => $slip->net_pay,
                    'generated_at' => $slip->created_at->format('d M, Y'),
                    'download_url' => route('employee.payslips.download', $slip->id)
                ];
            });

        return Inertia::render('Employee/Payslips/Index', [
            'payslips' => $payslips
        ]);
    }

    /**
     * Download Payslip PDF
     */
    public function download(Payslip $payslip)
    {
        // Security Check: Own payslip only
        if ($payslip->employee->user_id !== auth()->id()) {
             abort(403);
        }

        // We can reuse the logic from PayrollController or duplicate it here.
        // For DRY, let's reuse if possible, or easiest is to replicate the PDF generation lines 
        // since it is just 3 lines using DomPDF.
        
        $payslip->load(['employee.user', 'employee.department', 'payroll']);
        
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.payslip', [
            'payslip' => $payslip,
            'payroll' => $payslip->payroll
        ]);
        
        return $pdf->download('Payslip_' . $payslip->payslip_number . '.pdf');
    }
}
