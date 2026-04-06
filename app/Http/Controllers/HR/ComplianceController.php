<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\ComplianceChallan;
use App\Models\ComplianceConfig;
use App\Models\Payroll;
use App\Models\Employee;
use App\Services\Compliance\ECRGeneratorService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ComplianceController extends Controller
{
    protected $registerService; // Add Service Property

    public function __construct(ECRGeneratorService $ecrService, \App\Services\Compliance\StatutoryRegisterService $registerService)
    {
        $this->ecrService = $ecrService;
        $this->registerService = $registerService;
    }

    public function index(Request $request)
    {
        return Inertia::render('HR/Compliance/Index', [
            'tab' => $request->query('tab', 'dashboard'),
            'challans' => ComplianceChallan::orderBy('year', 'desc')->orderBy('month', 'desc')->get(),
            'configs' => ComplianceConfig::all()->keyBy('key'),
            'insights' => $this->getVarianceInsights()
        ]);
    }

    private function getVarianceInsights()
    {
        // Get last 2 processed payrolls
        $payrolls = Payroll::where('status', 'locked')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->take(2)
            ->get();

        if ($payrolls->count() < 2) return [];

        $current = $payrolls[0];
        $previous = $payrolls[1];
        
        // Mocking liability from payroll totals (Assuming stored in net_pay for now, but really should be sum of deductions)
        // In real app, we sum the deduction items.
        // Let's assume we can fetch total PF from items.
        
        $currentPF = $this->calculateTotalDeduction($current, 'PF');
        $prevPF = $this->calculateTotalDeduction($previous, 'PF');
        
        $insights = [];
        
        // PF Variance
        if ($prevPF > 0) {
            $diff = $currentPF - $prevPF;
            $pct = round(($diff / $prevPF) * 100, 1);
            if (abs($pct) > 5) { // Threshold 5%
                $trend = $diff > 0 ? 'increased' : 'decreased';
                $color = $diff > 0 ? 'text-red-600' : 'text-green-600'; // Increase is usually bad/warning unless hiring happened
                $insights[] = [
                    'type' => 'pf',
                    'message' => "PF Liability {$trend} by {$pct}% (₹" . number_format(abs($diff)) . ") vs last month.",
                    'color' => $color
                ];
            }
        }
        
        return $insights;
    }

    private function calculateTotalDeduction($payroll, $componentName)
    {
        // deeply inefficient but functional for small batch value-add demo
        // Ideally: Payroll::sum('pf_total')
        $total = 0;
        foreach($payroll->payslips as $item) {
             $deductions = $item->deductions_breakdown;
             $total += ($deductions[$componentName] ?? 0);
        }
        return $total;
    }
    
    // ... recordPayment ...
    public function recordPayment(Request $request) 
    {
        $validated = $request->validate([
            'month' => 'required|integer',
            'year' => 'required|integer',
            'type' => 'required|in:pf,esi,pt',
            'amount_paid' => 'required|numeric',
            'transaction_ref' => 'required|string',
            'payment_date' => 'required|date',
            'proof' => 'nullable|file|mimes:pdf,jpg,png'
        ]);

        $challan = ComplianceChallan::updateOrCreate(
            ['month' => $request->month, 'year' => $request->year, 'type' => $request->type],
            [
                'amount_paid' => $request->amount_paid,
                'transaction_ref' => $request->transaction_ref,
                'payment_date' => $request->payment_date,
                'status' => 'paid'
            ]
        );

        if ($request->hasFile('proof')) {
            $path = $request->file('proof')->store('compliance/proofs');
            $challan->update(['document_path' => $path]);
        }

        // Auto-Create Expense Entry
        $expenseCategory = \App\Models\ExpenseCategory::firstOrCreate(
            ['name' => 'Statutory Payments'],
            ['description' => 'PF, ESI, PT and other tax payments', 'is_active' => true]
        );

        \App\Models\Expense::create([
            // Assign to first Admin found or auth user if employee
            'employee_id' => auth()->user()->employee_id ?? \App\Models\Employee::first()->id, 
            'expense_category_id' => $expenseCategory->id,
            'category' => 'Statutory Payments',
            'amount' => $request->amount_paid,
            'title' => strtoupper($request->type) . " Payment for " . date('F', mktime(0, 0, 0, $request->month, 10)) . " " . $request->year,
            'incurred_date' => $request->payment_date,
            'status' => 'Approved', // Auto-approve as it's done by HR/Admin
            'is_billable' => false,
            'payout_method' => 'company_paid',
            'description' => "Auto-generated from Compliance Module. TRRN: " . $request->transaction_ref
        ]);

        return redirect()->back()->with('success', 'Payment recorded and Expense booked successfully.');
    }

    // ... mapping ...
    public function getPayrolls()
    {
        return response()->json(
            Payroll::orderBy('year', 'desc')
                ->orderBy('month', 'desc')
                ->get()
                ->map(fn($p) => [
                    'id' => $p->id,
                    'month_name' => date('F', mktime(0,0,0, $p->month, 10)),
                    'year' => $p->year
                ])
        );
    }

    public function getMissingUan()
    {
        return response()->json(
            Employee::whereNull('uan_number')
                ->where('status', 'active')
                ->select('id', 'first_name', 'last_name', 'department_id')
                ->with('department')
                ->get()
        );
    }
    
    public function updateMapping(Request $request, Employee $employee)
    {
        $request->validate([
            'uan_number' => 'nullable|string',
            'esi_number' => 'nullable|string',
            'pan_number' => 'nullable|string',
        ]);
        
        $employee->update($request->only(['uan_number', 'esi_number', 'pan_number']));
        
        return redirect()->back()->with('success', 'Mapping updated.');
    }

    // Tab C: ECR Generation
    public function generateEcr(Request $request)
    {
        $request->validate([
            'payroll_id' => 'required|exists:payrolls,id',
            'type' => 'required|in:pf,esi'
        ]);

        $payroll = Payroll::findOrFail($request->payroll_id);

        if ($request->type === 'pf') {
            $content = $this->ecrService->generatePFText($payroll);
            $filename = "PF_ECR_{$payroll->month}_{$payroll->year}.txt";
            $mime = 'text/plain';
        } else {
             // ESI Generation
            $content = $this->registerService->generateESIReturn($payroll);
            $filename = "ESI_Return_{$payroll->month}_{$payroll->year}.csv";
            $mime = 'text/csv';
        }
            
        return response($content)
            ->header('Content-Type', $mime)
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }

    // New: ECR Validation Endpoint
    public function validateBatch(Request $request) {
         $request->validate(['payroll_id' => 'required|exists:payrolls,id']);
         $payroll = Payroll::findOrFail($request->payroll_id);
         
         return response()->json($this->ecrService->validateBatch($payroll));
    }

    // PT Report
    public function downloadPt(Request $request) {
        $payroll = Payroll::findOrFail($request->payroll);
        $content = $this->registerService->generatePTReport($payroll);
        
        return response($content)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', "attachment; filename=\"PT_Report_{$payroll->month}_{$payroll->year}.csv\"");
    }
    public function previewBatch(Request $request) {
         $request->validate(['payroll_id' => 'required|exists:payrolls,id']);
         $payroll = Payroll::findOrFail($request->payroll_id);
         
         return response()->json($this->ecrService->getPreview($payroll));
    }

    // Tab D: Registers
    public function downloadRegister(Request $request)
    {
        $request->validate([
             'payroll_id' => 'required|exists:payrolls,id',
             'type' => 'required|in:form5,form10,form12a' 
        ]);
        
        $payroll = Payroll::findOrFail($request->payroll_id);
        $content = '';
        
        if ($request->type === 'form5') $content = $this->registerService->generateForm5($payroll);
        if ($request->type === 'form10') $content = $this->registerService->generateForm10($payroll);
        if ($request->type === 'form12a') $content = $this->registerService->generateForm12A($payroll);
        
        return response($content)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', "attachment; filename=\"{$request->type}_{$payroll->month}_{$payroll->year}.csv\"");
    }
    
    // Tab E: Config
    public function saveConfig(Request $request) {
        $data = $request->validate([
            'configs' => 'required|array'
        ]);
        
        foreach ($data['configs'] as $key => $value) {
            ComplianceConfig::updateOrCreate(
                ['key' => $key],
                ['value' => $value, 'type' => 'general'] // Using general for now
            );
        }
        
        return redirect()->back()->with('success', 'Configuration saved.');
    }
}
