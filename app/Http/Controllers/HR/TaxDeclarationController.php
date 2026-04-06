<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\TaxDeclaration;
use App\Models\TaxSection;
use App\Models\EmployeeTaxRegime;
use App\Models\InvestmentProof;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class TaxDeclarationController extends Controller
{
    public function index()
    {
        // 1. Fetch Pending Verifications for Hub
        $pending_verifications = TaxDeclaration::with(['employee', 'section'])
            ->where('status', 'Submitted') // Adjust based on your workflow status
            ->get();

        // 2. Fetch Active Employees for Form 16 list
        $employees = \App\Models\Employee::where('status', 'active')->get();

        // 3. Simple Stats
        $stats = [
            'total_pending' => $pending_verifications->count(),
            'verified_today' => TaxDeclaration::where('status', 'Verified')->whereDate('updated_at', now())->count()
        ];

        return Inertia::render('HR/Tax/TaxHub', [
            'pending_verifications' => $pending_verifications,
            'employees' => $employees,
            'stats' => $stats
        ]);
    }

    public function store(Request $request) 
    {
        $request->validate([
            'declarations' => 'array',
            'declarations.*.tax_section_id' => 'required|exists:tax_sections,id',
            'declarations.*.declared_amount' => 'required|numeric|min:0',
        ]);

        $employee = auth()->user()->employee;
        $fiscalYear = $request->fiscal_year;

        DB::transaction(function() use ($request, $employee, $fiscalYear) {
            foreach($request->declarations as $decl) {
                if ($decl['declared_amount'] > 0) {
                    TaxDeclaration::updateOrCreate(
                        [
                            'employee_id' => $employee->id,
                            'fiscal_year' => $fiscalYear,
                            'tax_section_id' => $decl['tax_section_id']
                        ],
                        [
                            'declared_amount' => $decl['declared_amount'],
                            'status' => 'Submitted' // Or Draft
                        ]
                    );
                }
            }
        });

        return back()->with('success', 'Investments Declared Successfully');
    }

    public function updateRegime(Request $request)
    {
        $request->validate([
            'regime' => 'required|in:Old,New',
            'fiscal_year' => 'required|string'
        ]);

        $employee = auth()->user()->employee;

        EmployeeTaxRegime::updateOrCreate(
            ['employee_id' => $employee->id, 'fiscal_year' => $request->fiscal_year],
            ['regime' => $request->regime]
        );

        return back()->with('success', 'Tax Regime Updated');
    }

    public function downloadComputation(Request $request)
    {
        $employeeId = auth()->user()->employee_id;
        $currentYear = now()->year; // Simplified. Ideally pass FY via request.
        if (now()->month <= 3) $currentYear--; // Adjust for Jan-Mar

        $service = new \App\Services\Payroll\TaxCalculatorService();
        $data = $service->getComputation($employeeId, $currentYear);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.tax_sheet', $data);
        return $pdf->download('Tax_Computation_Sheet.pdf');
    }
}
