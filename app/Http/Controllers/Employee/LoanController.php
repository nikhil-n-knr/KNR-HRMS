<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Loan;
use App\Models\LoanProduct;
use App\Services\Payroll\LoanService;
use App\Services\Loan\LoanEngineService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LoanController extends Controller
{
    protected $service;
    protected $engine;
    protected $riskService;

    public function __construct(LoanService $service, LoanEngineService $engine, \App\Services\Loans\LoanRiskService $riskService)
    {
        $this->service = $service;
        $this->engine = $engine;
        $this->riskService = $riskService;
    }
    
    public function index(Request $request)
    {
        $employee = auth()->user()->employee;
        if (!$employee) {
            return Inertia::render('Employee/Loans/Index', [
                'loans' => [],
                'products' => [],
                'error' => 'Your account is not linked to an Employee Profile. Please contact HR.'
            ]);
        }

        $loans = Loan::with('product')
            ->where('employee_id', $employee->id)
            ->latest()
            ->get();

        return Inertia::render('Employee/Loans/Index', [
            'loans' => $loans,
            'products' => LoanProduct::where('is_active', true)->get()
        ]);
    }

    public function create()
    {
        $employee = auth()->user()->employee;
        if (!$employee) {
            return redirect()->route('employee.loans.index')->with('error', 'No Employee Profile found.');
        }

        return Inertia::render('Employee/Loans/Create', [
            'products' => LoanProduct::where('is_active', true)->get()
        ]);
    }

    public function show(Loan $loan)
    {
        $employee = auth()->user()->employee;
        if (!$employee || $loan->employee_id !== $employee->id) {
            return back()->with('error', 'Unauthorized');
        }

        $loan->load(['product', 'repayments']);
        
        return Inertia::render('Employee/Loans/Show', [
            'loan' => $loan
        ]);
    }
    // public function index()
    // {
    //     return Inertia::render('Employee/Loans/Index', [
    //         'loans' => Loan::with(['product', 'repayments'])
    //             ->whereHas('employee', function($q) {
    //                 $q->where('user_id', auth()->id());
    //             })
    //             ->latest()
    //             ->get(),
    //         'products' => LoanProduct::where('is_active', true)->get()
    //     ]);
    // }
    
    public function products()
    {
        if (!\App\Models\SystemSetting::get('employee_loans_enabled', true)) {
            return response()->json([], 403);
        }

        return response()->json(
            LoanProduct::where('is_active', true)
                ->select('id', 'name', 'interest_rate', 'interest_type', 'max_amount_limit', 'max_tenure_months')
                ->get()
        );
    }

    public function status()
    {
        return response()->json([
            'enabled' => (bool) \App\Models\SystemSetting::get('employee_loans_enabled', true)
        ]);
    }

    public function simulate(Request $request) 
    {
        $validated = $request->validate([
            'loan_product_id' => 'required|exists:loan_products,id',
            'amount' => 'required|numeric|min:1',
            'tenure' => 'required|integer|min:1'
        ]);

        $product = LoanProduct::findOrFail($validated['loan_product_id']);
        $employee = auth()->user()->employee;
        if (!$employee) {
            return response()->json(['message' => 'No Employee Profile'], 403);
        }
        
        // 1. Check Eligibility (Engine)
        $eligibility = $this->engine->calculateEligibility(auth()->user(), $product);
        
        // 2. Calculate Risk (New Risk Service)
        $riskAnalysis = $this->riskService->analyzeRisk($employee, $validated['amount'], $validated['tenure'], $product);
        
        // 3. Calculate Financials (Engine)
        $rate = $this->engine->calculateInterestRate($product, $validated['amount'], $validated['tenure']);
        $schedule = $this->engine->generateAmortizationSchedule(
            $validated['amount'],
            $rate,
            $validated['tenure'],
            $product->interest_type
        );
        
        $emi = $schedule[0]['emi'] ?? 0;
        $totalInterest = collect($schedule)->sum('interest_component');
        $totalPayable = collect($schedule)->sum('emi');

        return response()->json([
            'eligibility' => $eligibility,
            'risk_analysis' => $riskAnalysis, // Return Risk Data
            'rate' => $rate,
            'emi' => $emi,
            'total_interest' => round($totalInterest, 2),
            'total_payable' => round($totalPayable, 2),
            'schedule' => array_slice($schedule, 0, 12)
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'loan_product_id' => 'required|exists:loan_products,id',
            'amount' => 'required|numeric|min:1',
            'tenure' => 'required|integer|min:1',
            'reason' => 'required|string|max:500',
            'signature_hash' => 'required|string', // E-Sign Simulation
            'agreed_to_terms' => 'accepted'
        ]);

        try {
            if (!auth()->user()->employee) {
                return back()->with('error', 'No Employee Profile found.');
            }
            $loan = $this->service->requestLoan($validated, auth()->id());
            
            // Update Digital Trust
            $loan->update([
                'signed_at' => now(),
                'signer_ip' => $request->ip(),
                'signature_hash' => $validated['signature_hash']
            ]);

            return redirect()->route('employee.loans.index')->with('success', 'Loan Application Submitted Successfully.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
