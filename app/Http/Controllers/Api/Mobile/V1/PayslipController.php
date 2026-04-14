<?php

namespace App\Http\Controllers\Api\Mobile\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Payslip;
use App\Models\Payroll;
use App\Services\Infrastructure\LoggerService;
use Illuminate\Support\Facades\Storage;

class PayslipController extends Controller
{
    /**
     * List all available payslips for the operative
     */
    public function index(Request $request)
    {
        $employee = $request->user()->employee;

        if (!$employee) {
            return response()->json(['message' => 'Employee profile not found.'], 404);
        }

        $payslips = Payslip::with('payroll')
            ->where('employee_id', $employee->id)
            ->whereIn('status', ['Published', 'Paid']) // Match web logic + correct casing
            ->orderBy(
                Payroll::select('year')->whereColumn('payrolls.id', 'payslips.payroll_id'),
                'desc'
            )
            ->orderBy(
                Payroll::select('month')->whereColumn('payrolls.id', 'payslips.payroll_id'),
                'desc'
            )
            ->get()
            ->map(function($slip) {
                return [
                    'id' => $slip->id,
                    'month' => $slip->payroll->month,
                    'year' => $slip->payroll->year,
                    'month_label' => \Carbon\Carbon::createFromDate($slip->payroll->year, $slip->payroll->month, 1)->format('M Y'),
                    'gross_pay' => $slip->gross_earnings,
                    'net_pay' => $slip->net_pay,
                    'status' => $slip->status,
                    'file_path' => $slip->file_path
                ];
            });

        return response()->json($payslips);
    }

    /**
     * Download specific payslip
     */
    public function download(Request $request)
    {
        $employee = $request->user()->employee;
        $month = $request->query('month');
        $year = $request->query('year');

        if (!$employee) {
            return response()->json(['message' => 'Employee profile not found.'], 404);
        }

        $payslip = Payslip::where('employee_id', $employee->id)
            ->whereHas('payroll', function($q) use ($month, $year) {
                $q->where('month', $month)->where('year', $year);
            })
            ->first();

        if (!$payslip) {
            return response()->json(['message' => 'Payslip not found for this period.'], 404);
        }

        if (!$payslip->file_path || !Storage::exists($payslip->file_path)) {
            // If file doesn't exist, we might need to generate it or return an error
            // For now, assume it exists in storage as per the model
            return response()->json(['message' => 'Payslip file is missing from archive.'], 404);
        }

        \Log::context(['user_id' => auth()->id(), 'payslip_id' => $payslip->id]);
        LoggerService::info('Operative Downloaded Payslip');

        return Storage::download($payslip->file_path, "Payslip_{$month}_{$year}.pdf");
    }
}
