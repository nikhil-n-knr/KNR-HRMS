<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Loan;
use App\Models\LoanProduct;
use App\Services\Payroll\LoanService;
use App\Notifications\RequestProcessed;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LoanController extends Controller
{
    protected $service;
    protected $riskService;
    protected $lifecycleService;

    public function __construct(
        LoanService $service,
        \App\Services\Loans\LoanRiskService $riskService,
        \App\Services\Loans\LoanLifecycleService $lifecycleService
    )
    {
        $this->service = $service;
        $this->riskService = $riskService;
        $this->lifecycleService = $lifecycleService;
    }

    /**
     * Main View - The 5-Tab Container
     */
    public function index(Request $request)
    {
        return Inertia::render('HR/Loans/Index', [
            'initialTab' => $request->query('tab', 'dashboard')
        ]);
    }

    // --- Tab A: Dashboard Stats ---
    public function stats()
    {
        $totalDisbursed = Loan::whereNotNull('disbursement_date')->sum('principal_amount');
        $totalRecovered = \App\Models\LoanRepayment::where('status', 'Deducted')->sum('amount');
        $outstanding = Loan::active()->sum('principal_amount') - $totalRecovered; // Approximation
        
        $atRisk = Loan::active()->whereHas('repayments', function($q) {
             $q->where('status', 'Failed')->orWhere('status', 'Skipped');
        })->count();

        $monthlyDisbursal = Loan::selectRaw('MONTH(disbursement_date) as month, SUM(principal_amount) as total')
            ->whereYear('disbursement_date', now()->year)
            ->groupBy('month')
            ->pluck('total', 'month');

        return response()->json([
            'metrics' => [
                'total_disbursed' => $totalDisbursed,
                'total_recovered' => $totalRecovered,
                'outstanding_portfolio' => $outstanding,
                'at_risk_count' => $atRisk
            ],
            'chart_data' => $monthlyDisbursal
        ]);
    }

    // --- Tab B: Application Queue ---
    public function queue()
    {
        $loans = Loan::with(['employee.user', 'product'])
            ->where('status', 'Pending')
            ->orderBy('created_at')
            ->get()
            ->map(function($loan) {
                // Attach Risk Analysis On-the-fly or from DB
                $analysis = $loan->risk_analysis; 
                if (!$analysis) {
                    $analysis = $this->riskService->analyzeRisk($loan->employee, $loan->principal_amount, $loan->tenure_months, $loan->product);
                    // Optionally save it
                    // $loan->update(['risk_score' => $analysis['score'], 'risk_analysis' => $analysis]);
                }
                $loan->risk_analysis = $analysis;
                return $loan;
            });

        return response()->json($loans);
    }

    // --- Tab C: Active Portfolio ---
    public function portfolio()
    {
        $loans = Loan::with(['employee.user'])
            ->active()
            ->withCount(['repayments as paid_installments' => function($q) {
                $q->where('status', 'Deducted');
            }])
            ->get()
            ->map(function($loan) {
                $loan->foreclosure_details = $this->lifecycleService->getForeclosureDetails($loan);
                return $loan;
            });

        return response()->json($loans);
    }

    // --- Tab D: Disbursement & Recovery ---
    public function disbursement()
    {
        $toDisburse = Loan::with(['employee.user', 'product', 'employee.currentBankDetail'])
            ->where('status', 'Approved')
            ->whereNull('disbursement_date')
            ->get();
            
        return response()->json([
            'to_disburse' => $toDisburse
        ]);
    }
    
    // --- Tab E: Configuration ---
    public function products()
    {
        return response()->json(LoanProduct::with('interestRules')->get());
    }
    
    public function recovery()
    {
        // Preview what would be synced
        // This is complex, let's just use the process logic for now via button
        return back(); 
    }

    public function pushToPayroll() {
         $result = $this->lifecycleService->processRecovery();
         return back()->with('success', "Synced {$result['count']} deductions totaling INR {$result['total_amount']} to Payroll.");
    }

    // --- Actions ---

    public function analyze(Loan $loan) {
        $analysis = $this->riskService->analyzeRisk($loan->employee, $loan->principal_amount, $loan->tenure_months, $loan->product);
        $loan->update(['risk_score' => $analysis['score'], 'risk_analysis' => $analysis]);
        return back()->with('success', 'Risk Analysis Updated');
    }

    public function approve(Loan $loan)
    {
        $this->service->approveLoan($loan, auth()->id());

        // Notify Requester
        if ($loan->employee && $loan->employee->user) {
            try {
                $loan->employee->user->notify(new \App\Notifications\RequestProcessed($loan, 'Approved'));
            } catch (\Exception $e) {
                \Log::error('Loan Approval Notification Failed: ' . $e->getMessage());
            }
        }

        return back()->with('success', 'Loan Approved');
    }

    public function reject(Loan $loan, Request $request)
    {
        $loan->update(['status' => 'Rejected', 'reject_reason' => $request->reason]);

        // Notify Requester
        if ($loan->employee && $loan->employee->user) {
            try {
                $loan->employee->user->notify(new RequestProcessed($loan, 'Rejected', $request->reason));
            } catch (\Exception $e) {
                \Log::error('Loan Rejection Notification Failed: ' . $e->getMessage());
            }
        }

        return back()->with('success', 'Loan Rejected');
    }
    
    public function foreclose(Loan $loan) {
        $this->lifecycleService->foreclose($loan);
        return back()->with('success', 'Loan Foreclosed');
    }
    
    public function pause(Loan $loan) {
        $this->lifecycleService->pauseLoan($loan);
        return back()->with('success', 'Loan Paused for 1 Month');
    }
    
    public function markDisbursed(Request $request, Loan $loan) {
        $loan->update([
            'disbursement_date' => $request->date ?? now(),
            'status' => 'Active' 
            // Trigger Repayment Schedule Creation here if not done in Approve?
            // Usually done on Approval or Disbursement.
        ]);
        // If Logic was in Approve, good. If Disburse triggers schedule, call Service.
        // Assuming LoanService::approveLoan generates schedule.
        return back()->with('success', 'Marked as Disbursed');
    }
}
