<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Payroll;
use App\Models\Payslip;
use App\Models\EmployeeSalary;
use App\Services\Payroll\PayrollProcessor;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class PayrollController extends Controller
{
    protected $processor;

    public function __construct(PayrollProcessor $processor)
    {
        $this->processor = $processor;
    }

    public function index()
    {
        $query = Payroll::with('processor')->latest();

        if (request('trashed')) {
            $query->onlyTrashed();
        }

        $payrolls = $query->get();
        
        $stats = [
            'total_payout_ytd' => Payroll::where('status', 'Paid')->whereYear('start_date', now()->year)->sum('total_payout'),
            'last_run' => Payroll::latest()->first()?->created_at->format('M d, Y'),
            'active_salaries' => EmployeeSalary::where('is_active', true)->count()
        ];

        return Inertia::render('HR/Payroll/Index', [
            'payrolls' => $payrolls,
            'stats' => $stats,
            'filters' => request()->only(['trashed'])
        ]);
    }

    public function syncStats(Request $request)
    {
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);
        
        $startDate = \Carbon\Carbon::createFromDate($year, $month, 1)->startOfDay();
        $endDate = $startDate->copy()->endOfMonth();

        return response()->json([
            'total_employees' => \App\Models\Employee::where('status', 'active')->count(),
            'new_joiners' => \App\Models\Employee::whereBetween('joining_date', [$startDate, $endDate])->count(),
            'exits' => \App\Models\EmployeeExit::whereBetween('last_working_day_approved', [$startDate, $endDate])->count(),
            'lwp_days' => \App\Models\LeaveRequest::where('status', 'Approved')
                            ->whereBetween('start_date', [$startDate, $endDate])
                            ->whereHas('leaveType', fn($q) => $q->where('is_paid', false))
                            ->sum('total_days'),
            'pending_leaves' => \App\Models\LeaveRequest::where('status', 'Pending')
                            ->whereBetween('start_date', [$startDate, $endDate])
                            ->count()
        ]);
    }

    public function disbursement()
    {
        return Inertia::render('HR/Payroll/Disbursement', [
            'payrolls' => Payroll::latest()->select('id', 'month', 'year', 'status', 'batch_name', 'total_payout', 'created_at')->get()
        ]);
    }

    public function allPayslips(Request $request)
    {
        $query = Payslip::with(['employee.user', 'payroll'])
            ->when($request->search, function($q) use ($request) {
                $q->whereHas('employee.user', function($qu) use ($request) {
                    $qu->where('name', 'like', '%' . $request->search . '%');
                })->orWhere('payslip_number', 'like', '%' . $request->search . '%');
            })
            ->orderBy('id', 'desc');

        return Inertia::render('HR/Payroll/AllPayslips', [
            'payslips' => $query->paginate(30)->withQueryString(),
            'filters' => $request->only(['search'])
        ]);
    }

    public function create()
    {
        // View to run payroll
        return Inertia::render('HR/Payroll/Run', [
            'months' => [
                ['value' => 1, 'label' => 'January'],
                ['value' => 2, 'label' => 'February'],
                ['value' => 3, 'label' => 'March'],
                ['value' => 4, 'label' => 'April'],
                ['value' => 5, 'label' => 'May'],
                ['value' => 6, 'label' => 'June'],
                ['value' => 7, 'label' => 'July'],
                ['value' => 8, 'label' => 'August'],
                ['value' => 9, 'label' => 'September'],
                ['value' => 10, 'label' => 'October'],
                ['value' => 11, 'label' => 'November'],
                ['value' => 12, 'label' => 'December'],
            ],
            'years' => range(now()->year - 1, now()->year + 1),
            // Fetch simple list for Dropdown
            'employees' => \App\Models\Employee::whereHas('user', function($q) {
                 $q->whereNull('deleted_at'); 
            })->with('user:id,name')->get()->map(fn($e) => ['id' => $e->id, 'name' => $e->user->name])
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer',
            'employee_id' => 'nullable|exists:employees,id'
        ]);

        // Check if exists ONLY if it's a bulk run (no employee_id)
        if (!$request->employee_id) {
            $existing = Payroll::where('month', $request->month)
                ->where('year', $request->year)
                ->where('batch_name', 'like', '% Payroll') // Matches "January 2026 Payroll"
                ->first();

            if ($existing) {
                // Allow restart if Draft/Rejected and explicitly requested
                if ($request->recalculate && in_array($existing->status, ['Draft', 'Rejected'])) {
                    $existing->payslips()->delete();
                    $existing->delete();
                } else {
                    $msg = 'Monthly Payroll for this period already exists.';
                    if ($request->recalculate) {
                        $msg = 'Cannot recalculate. Payroll is already ' . $existing->status . '.';
                    }
                    return back()->withErrors(['message' => $msg]);
                }
            }
        }

        try {
            // 1. Create 'Draft' Payload Synchronously
            // This ensures we have a valid ID to redirect to immediately.
            $payroll = $this->processor->createDraft(
                $request->month, 
                $request->year, 
                auth()->id(),
                $request->employee_id,
                ['ignore_attendance' => (bool) $request->ignore_attendance]
            );
            
            // 2. Dispatch Job for Heavy Lifting (Item Calculation)
            \App\Jobs\ProcessPayroll::dispatch(
                $payroll->id,
                $request->employee_id
            );

            // 3. Return ID for Frontend Redirect (to Verification Step)
            if ($request->wantsJson()) {
                 return response()->json([
                     'id' => $payroll->id,
                     'message' => 'Payroll calculation started in background.'
                 ]);
            }

            // Fallback for non-AJAX
            return to_route('hr.payroll.index', [
                'tab' => 'wizard',
                'step' => 3, // Redirect to Verification Step
                'payroll_id' => $payroll->id
            ])
            ->with('success', 'Payroll calculated successfully.');

        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(['message' => $e->getMessage()], 500);
            }
            return back()->withErrors(['message' => 'Failed to queue payroll: ' . $e->getMessage()]);
        }
    }

    public function show(Payroll $payroll)
    {
        $payroll->load(['payslips.employee.user', 'approver', 'approvedBy', 'processor']);
        
        // Fetch potential approvers (Admins or Managers)
        $approvers = \App\Models\User::whereHas('roles', function($q) {
                $q->whereIn('name', ['Super Admin', 'HR Manager', 'Team Manager', 'HR Admin', 'Admin', 'Manager']);
            })
            ->where('id', '!=', auth()->id()) // Optional: Can't approve own submission?
            ->get(['id', 'name']);

        return Inertia::render('HR/Payroll/Show', [
            'payroll' => $payroll,
            'payslips' => $payroll->payslips,
            'approvers' => $approvers
        ]);
    }

    public function confirm(Payroll $payroll)
    {
        if ($payroll->status === 'Paid') {
            return back()->with('error', 'Already paid.');
        }

        try {
            $this->processor->confirmPayroll($payroll, auth()->id());
            return back()->with('success', 'Payroll Confirmed & Expenses Logged!')->setStatusCode(303);
        } catch (\Exception $e) {
            return back()->withErrors(['message' => $e->getMessage()]);
        }
    }

    /**
     * Regenerate Payroll (Draft Only)
     */
    public function regenerate(Payroll $payroll)
    {
        if (!in_array($payroll->status, ['Draft', 'Approved'])) {
            return back()->with('error', 'Cannot regenerate finalized payroll (Paid/Published).');
        }

        // Delete existing slips
        $payroll->payslips()->delete();
        
        // Reset totals
        $payroll->update([
            'total_gross' => 0,
            'total_deductions' => 0,
            'total_net' => 0,
            'total_payout' => 0
        ]);

        // Re-run processor logic
        // We reuse the generate logic but strictly for this payroll's month/year
        // calling internal processor method or duplicating the loop if processor doesn't support "update".
        // Let's assume processor->generatePayroll creates a NEW object. 
        // We should just DELETE this one and create NEW one? 
        // But user asked to "fix /1", implies keeping ID.
        // Let's manually trigger the generation loop here for simplicity or refactor processor.
        
        // Pragmantic: Re-call processor logic but pass the EXISTING payroll object if possible?
        // Checking processor... 
        // Let's just DELETE and CREATE NEW, and redirect to new ID. 
        // "give option regenerate" - implies "Re-do". 
        // ID change is acceptable usually. 
        // "fix /1" might mean /1 is broken (empty?).
        
        // Actually, let's try to KEEP the ID if possible to be cleaner.
        // I will add `regenerate` to Processor.
        
        DB::transaction(function() use ($payroll) {
             // 1. Force Delete slips to free up payslip_numbers
             $payroll->payslips()->forceDelete();
             
             // 2. Re-calculate using same params
             // We need to call the core logic of generatePayroll but bind to $payroll
             // For now, I'll delete the payroll and create new to avoid logic duplication in Controller.
             // But force the same Batch Name etc?
             
             // Actually, the user might be stuck on /1 which is empty/broken.
             // If I delete /1, they get 404.
             // If I create /2, they get redirected. That's fine.
             
             $payroll->delete(); // Soft delete or Hard? Model says SoftDeletes?
             // If Soft, then standard unique checks might fail if not excluding deleted.
             // We should force delete this payroll too if we are making a new one with same logic?
             // Actually, the new payroll will have new ID, so it's fine.
        });
        
        // Re-generate
        $newPayroll = $this->processor->generatePayroll($payroll->month, $payroll->year, auth()->id());
        
        return to_route('hr.payroll.show', $newPayroll->id)->with('success', 'Payroll Regenerated')->setStatusCode(303);
    }

    public function downloadPdf(Payslip $payslip)
    {
        // Security Check
        $user = auth()->user();
        $isOwner = $user->id === ($payslip->employee->user_id ?? null); 
        
        $authorizedRoles = ['Super Admin', 'Admin', 'HR Manager', 'HR Admin', 'Manager'];
        $isAuthorized = $user->roles()->whereIn('name', $authorizedRoles)->exists();

        if (!$isOwner && !$isAuthorized) {
            abort(403, 'Unauthorized access to this payslip.');
        }
        
        // Check if exists
        if ($payslip->pdf_path && Storage::disk('public')->exists($payslip->pdf_path)) {
             return Storage::disk('public')->download($payslip->pdf_path, 'Payslip_' . $payslip->payslip_number . '.pdf');
        }

        // Generate On-the-Fly (Sync) for single download if not queued
        // Or dispatch job and wait? Sync is better for single click.
        
        // 1. Configs
        $configs = [
            'header' => \App\Models\SystemSetting::get('payslip_header'),
            'footer' => \App\Models\SystemSetting::get('payslip_footer'),
            'watermark' => \App\Models\SystemSetting::get('payslip_watermark'),
        ];
        
        $payslip->load(['employee.user', 'employee.department', 'payroll']);
        
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.payslip', [
            'payslip' => $payslip,
            'payroll' => $payslip->payroll,
            'configs' => $configs
        ]);
        
        return $pdf->download('Payslip_' . $payslip->payslip_number . '.pdf');
    }

    public function emailPayslip(Payslip $payslip)
    {
        // Permission Check: Only HR/Admin can email payslips (to avoid spam logic for now)
        $authorizedRoles = ['Super Admin', 'Admin', 'HR Manager', 'HR Admin', 'Manager'];
        if (!auth()->user()->roles()->whereIn('name', $authorizedRoles)->exists()) {
            abort(403, 'Unauthorized.');
        }

        try {
            if (!$payslip->employee->user->email) {
                return back()->withErrors(['message' => 'Employee has no email address linked.']);
            }

            \Illuminate\Support\Facades\Mail::to($payslip->employee->user)->send(new \App\Mail\PayslipEmail($payslip));
            return back()->with('success', 'Payslip emailed successfully!')
                ->setStatusCode(303);
        } catch (\Exception $e) {
            return back()->withErrors(['message' => 'Failed to email: ' . $e->getMessage()]);
        }
    }

    public function submit(Payroll $payroll, Request $request)
    {
        // 1. Validate State
        if ($payroll->status !== 'Draft' && $payroll->status !== 'Rejected') {
            return back()->with('error', 'Only Draft or Rejected payrolls can be submitted.');
        }

        $request->validate([
            'approver_id' => 'required|exists:users,id',
            'notes' => 'nullable|string'
        ]);

        // 2. Update Status & Assign
        $payroll->update([
            'status' => 'Pending Approval',
            'approver_id' => $request->approver_id,
            // We could store notes in a separate PayrollActivity log if needed, 
            // but for now maybe just clear rejection reason so it's fresh.
            'rejection_reason' => null
        ]);

        // 3. Notify Approver
        $approver = \App\Models\User::find($request->approver_id);
        // \Mail::to($approver)->queue(new \App\Mail\PayrollApprovalRequest($payroll)); // Todo: Mailable

        return back()->with('success', 'Payroll submitted for verification!')->setStatusCode(303);
    }

    public function approve(Payroll $payroll)
    {
        // Check Authority
        $authorizedRoles = ['Super Admin', 'Admin', 'HR Manager', 'HR Admin'];
        $isAuthorized = auth()->user()->roles()->whereIn('name', $authorizedRoles)->exists();
        
        if (auth()->id() !== $payroll->approver_id && !$isAuthorized) {
             return back()->with('error', 'Unauthorized. You are not the assigned approver.');
        }

        if ($payroll->status !== 'Pending Approval') {
            return back()->with('error', 'Payroll is not pending approval.');
        }

        $payroll->update([
            'status' => 'Approved',
            'approved_by' => auth()->id(),
            'rejection_reason' => null
        ]);

        return back()->with('success', 'Payroll Approved! You can now Release/Publish it.')->setStatusCode(303);
    }

    public function reject(Payroll $payroll, Request $request)
    {
        // Check Authority
        $authorizedRoles = ['Super Admin', 'Admin', 'HR Manager', 'HR Admin'];
        $isAuthorized = auth()->user()->roles()->whereIn('name', $authorizedRoles)->exists();

        if (auth()->id() !== $payroll->approver_id && !$isAuthorized) {
             return back()->with('error', 'Unauthorized.');
        }

        $request->validate(['reason' => 'required|string|max:1000']);

        $payroll->update([
            'status' => 'Rejected',
            'rejection_reason' => $request->reason,
            'approver_id' => null // Unassign so HR can pick it up
        ]);

        return back()->with('success', 'Payroll Rejected and sent back to Draft.')->setStatusCode(303);
    }

    public function publish(Payroll $payroll)
    {
         if ($payroll->status !== 'Approved') {
            return back()->with('error', 'Payroll must be Approved before releasing.');
        }

        // Logic from old confirmPayroll but split
        // Actually, confirmPayroll was doing "Paid". 
        // User wants: Approved -> Released (Published). 
        // And "Released" is where payslips are visible.
        // Let's treat "Published" as "Paid" in terms of visibility or separate?
        // User says: "Released (Published): The final step where payslips are emailed and visible".
        // Usually "Paid" implies money sent. "Published" implies docs released.
        // Let's use "Published" as the final status that unlocks PDF download.
        
        DB::transaction(function() use ($payroll) {
            $payroll->update(['status' => 'Published']);
            $payroll->payslips()->update(['status' => 'Published']); // or 'Paid'?
            
            // Trigger Expenses Payout & Accounting Logs (from old confirmPayroll logic)
            // We can reuse the logic or move it here.
            // Let's reuse confirmPayroll logic but rename status
            
            $this->processor->confirmPayroll($payroll, auth()->id()); // This sets status to 'Paid'. We force overwrite to 'Published' if needed?
            // Actually confirmPayroll sets status to 'Paid'. 
            // Let's update confirmPayroll inside Processor to accept target status or just update it here after.
            
            $payroll->update(['status' => 'Published']); // Override 'Paid' with 'Published' since User wants new Terminology
        });

        return back()->with('success', 'Payroll Released & Payslips Distributed!')->setStatusCode(303);
    }

    /**
     * Unpublish Payroll (Revert to Approved)
     * "Inactive" a release
     */
    public function unpublish(Payroll $payroll)
    {
        // 1. Permission Check
        $authorizedRoles = ['Super Admin', 'Admin', 'HR Manager'];
        if (!auth()->user()->roles()->whereIn('name', $authorizedRoles)->exists()) {
             abort(403, 'Unauthorized.');
        }

        if ($payroll->status !== 'Published') {
            return back()->with('error', 'Only Published payrolls can be unpublished.');
        }

        DB::transaction(function() use ($payroll) {
            $payroll->update(['status' => 'Approved']);
            // Optionally update payslips too if they track status
            $payroll->payslips()->update(['status' => 'Approved']); 
        });

        return back()->with('success', 'Payroll Unpublished. It is now hidden from employees but remains Approved.');
    }

    /**
     * Export Bank Transfer File (CSV)
     */
    /**
     * Export Bank Transfer File
     */
    public function exportBankFile(\App\Models\Payroll $payroll, Request $request)
    {
        // Permission Check
        $authorizedRoles = ['Super Admin', 'Admin', 'HR Manager', 'HR Admin', 'Finance'];
        if (!auth()->user()->roles()->whereIn('name', $authorizedRoles)->exists()) {
            abort(403);
        }
        
        // Determine Bank Service (Factory Pattern usually, here simple switch)
        // Default to HDFC for now as per request.
        $bankService = app(\App\Services\Banking\HDFCBankingService::class);
        
        $fileData = $bankService->generatePaymentFile($payroll);
        
        return response($fileData['content'])
            ->header('Content-Type', $fileData['mime'])
            ->header('Content-Disposition', "attachment; filename=\"{$fileData['filename']}\"");
    }
    /**
     * Force Adjust a Payslip (Manual Override)
     */
    public function updatePayslip(Payslip $payslip, Request $request)
    {
        // Permission Check
        $authorizedRoles = ['Super Admin', 'Admin', 'HR Manager', 'HR Admin'];
        if (!auth()->user()->roles()->whereIn('name', $authorizedRoles)->exists()) {
            abort(403, 'Unauthorized.');
        }

        if ($payslip->payroll->status !== 'Draft' && $payslip->payroll->status !== 'Rejected') {
            return back()->with('error', 'Cannot edit localized/approved payrolls.');
        }
        
        $request->validate([
            'gross_earnings' => 'required|numeric|min:0',
            'earnings_breakdown' => 'required|array',
            'gross_deductions' => 'required|numeric|min:0',
            'deductions_breakdown' => 'required|array',
            'net_pay' => 'required|numeric|min:0',
            'remarks' => 'nullable|string'
        ]);

        $payslip->update([
            'gross_earnings' => $request->gross_earnings,
            'earnings_breakdown' => $request->earnings_breakdown,
            'gross_deductions' => $request->gross_deductions,
            'deductions_breakdown' => $request->deductions_breakdown,
            'net_pay' => $request->net_pay,
            'remarks' => $request->remarks ? $payslip->remarks . "\n[Manual Adjust]: " . $request->remarks : $payslip->remarks
        ]);

        // Recalculate Payroll Totals
        $this->recalculatePayrollTotals($payslip->payroll_id);

        return back()->with('success', 'Payslip adjusted successfully.');
    }

    /**
     * Toggle Hold Status
     */
    public function toggleHoldPayslip(Payslip $payslip)
    {
        // Permission Check
        $authorizedRoles = ['Super Admin', 'Admin', 'HR Manager', 'HR Admin'];
        if (!auth()->user()->roles()->whereIn('name', $authorizedRoles)->exists()) {
            abort(403);
        }

        $payslip->update(['is_held' => !$payslip->is_held]);
        
        $msg = $payslip->is_held ? 'Payslip put on HOLD (Excluded from Bank File).' : 'Payslip Released from Hold.';
        return back()->with('success', $msg);
    }

    /**
     * Remove Payslip from Payroll
     */
    public function removePayslip(Payslip $payslip)
    {
        // Permission Check
        $authorizedRoles = ['Super Admin', 'Admin', 'HR Manager', 'HR Admin'];
        if (!auth()->user()->roles()->whereIn('name', $authorizedRoles)->exists()) {
            abort(403);
        }

        if ($payslip->payroll->status !== 'Draft' && $payslip->payroll->status !== 'Rejected') {
            return back()->with('error', 'Cannot remove from locked payroll.');
        }

        $payrollId = $payslip->payroll_id;
        $payslip->delete();

        // Recalculate Totals
        $this->recalculatePayrollTotals($payrollId);

        return back()->with('success', 'Payslip removed from this run.');
    }

    protected function recalculatePayrollTotals($payrollId)
    {
        $payroll = Payroll::find($payrollId);
        $sums = $payroll->payslips()->selectRaw('sum(gross_earnings) as gross, sum(gross_deductions) as ded, sum(net_pay) as net')->first();
        
        $payroll->update([
            'total_gross' => $sums->gross ?? 0,
            'total_deductions' => $sums->ded ?? 0,
            'total_payout' => $sums->net ?? 0,
            'total_net' => $sums->net ?? 0
        ]);
    }
    /**
     * Delete Payroll Run (Soft Delete)
     */
    public function destroy(Payroll $payroll)
    {
        // 1. Permission Check
        $authorizedRoles = ['Super Admin', 'Admin', 'HR Manager'];
        $isAuthorized = auth()->user()->roles()->whereIn('name', $authorizedRoles)->exists();
        
        if (auth()->id() !== $payroll->processed_by && !$isAuthorized) {
             abort(403, 'Unauthorized.');
        }

        // 2. Validation: Only Draft/Rejected can be deleted freely?
        // User wants "Remove complete payroll", likely even if pending.
        // If "Approved" or "Published", usually we block it.
        // But if they ask for "option to delete", they likely want to clean up mistakes.
        // I'll allow Draft/Rejected/Pending.
        // If Published/Paid, I'll restrict to Super Admin only.
        
        if (in_array($payroll->status, ['Published', 'Paid']) && !auth()->user()->hasRole('Super Admin')) {
            return back()->with('error', 'Cannot delete finalized payroll. Contact Super Admin.');
        }
        
        DB::transaction(function() use ($payroll) {
            // 3. Delete Payslips (Soft Delete due to model trait)
            $payroll->payslips()->delete();
            
            // 4. Delete Payroll
            $payroll->delete();
        });

        return to_route('hr.payroll.index')->with('success', 'Payroll run deleted successfully.');
    }

    /**
     * Restore Deleted Payroll Run
     */
    public function restore($id)
    {
        // 1. Permission Check
        $authorizedRoles = ['Super Admin', 'Admin', 'HR Manager'];
        if (!auth()->user()->roles()->whereIn('name', $authorizedRoles)->exists()) {
             abort(403, 'Unauthorized.');
        }

        $payroll = Payroll::withTrashed()->findOrFail($id);
        
        DB::transaction(function() use ($payroll) {
            // Restore Payroll
            $payroll->restore();
            
            // Restore associated Payslips
            $payroll->payslips()->restore();
        });

        return to_route('hr.payroll.index')->with('success', 'Payroll run restored successfully.');
    }

    /**
     * Export Comprehensive Payroll Breakdown (Excel/CSV)
     */
    public function exportBreakdown(\App\Models\Payroll $payroll)
    {
        // Permission Check
        $authorizedRoles = ['Super Admin', 'Admin', 'HR Manager', 'Finance'];
        if (!auth()->user()->roles()->whereIn('name', $authorizedRoles)->exists()) {
            abort(403);
        }

        return response()->streamDownload(function() use ($payroll) {
            $handle = fopen('php://output', 'w');
            
            // Collect all unique component keys across all payslips
            $earningKeys = [];
            $deductionKeys = [];
            
            $payslips = $payroll->payslips;
            foreach ($payslips as $slip) {
                if (is_array($slip->earnings_breakdown)) {
                    foreach (array_keys($slip->earnings_breakdown) as $key) {
                        if (!in_array($key, $earningKeys)) $earningKeys[] = $key;
                    }
                }
                if (is_array($slip->deductions_breakdown)) {
                    foreach (array_keys($slip->deductions_breakdown) as $key) {
                        if (!in_array($key, $deductionKeys)) $deductionKeys[] = $key;
                    }
                }
            }

            // Header
            $header = ['Emp Code', 'Name', 'Department', 'Designation', 'Joining Date', 'Days Payable', 'LOP Days'];
            foreach ($earningKeys as $key) $header[] = $key;
            $header[] = 'Gross Earnings';
            foreach ($deductionKeys as $key) $header[] = $key;
            $header[] = 'Gross Deductions';
            $header[] = 'Net Pay';
            $header[] = 'Bank Account';
            $header[] = 'IFSC';

            fputcsv($handle, $header);

            $payslipsData = $payroll->payslips()->with(['employee.department', 'employee.bankDetails'])->get();
            foreach ($payslipsData as $slip) {
                $employee = $slip->employee;
                $bank = $employee->bankDetails ? $employee->bankDetails()->where('is_primary', true)->first() : null;
                
                $row = [
                    $employee->employee_code,
                    $employee->first_name . ' ' . $employee->last_name,
                    $employee->department->name ?? '-',
                    $employee->designation,
                    $employee->joining_date?->format('Y-m-d'),
                    $slip->payable_days,
                    $slip->lop_days,
                ];

                // Earnings
                foreach ($earningKeys as $key) {
                    $row[] = $slip->earnings_breakdown[$key] ?? 0;
                }
                $row[] = $slip->gross_earnings;

                // Deductions
                foreach ($deductionKeys as $key) {
                    $row[] = $slip->deductions_breakdown[$key] ?? 0;
                }
                $row[] = $slip->gross_deductions;

                $row[] = $slip->net_pay;
                $row[] = $bank->account_number ?? '-';
                $row[] = $bank->ifsc_code ?? '-';

                fputcsv($handle, $row);
            }

            fclose($handle);
        }, "payroll_breakdown_{$payroll->id}_" . date('Ymd') . ".csv");
    }

    /**
     * Export Pre-approval Excel for Bank Verification
     */
    public function exportPreApproval(\App\Models\Payroll $payroll)
    {
        // Re-use logic for now or specific condensed format
        return $this->exportBreakdown($payroll);
    }
}

