<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\TaxDeclaration;
use App\Models\TaxSection;
use App\Models\EmployeeTaxRegime;
use App\Models\InvestmentProof;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class TaxController extends Controller
{
    /**
     * Fetch Tax Data (Regime, Sections, Declarations)
     */
    /**
     * Fetch Tax Data (Regime, Sections, Declarations, HRA)
     */
    public function index()
    {
        $employee = auth()->user()->employee;
        if (!$employee) {
            return response()->json([
                'error' => 'Your account is not linked to an Employee Profile. Please contact HR.',
                'sections' => [],
                'declarations' => [],
                'hra' => null,
                'regime' => null,
                'locked' => true
            ], 200); // Return empty state with message instead of 404
        }

        $currentMonth = now()->month;
        $year = now()->year;
        $fiscalYear = ($currentMonth > 3) ? "$year-".($year+1) : ($year-1)."-$year";

        $sections = TaxSection::where('is_active', true)->get();
        
        $declarations = TaxDeclaration::where('employee_id', $employee->id)
            ->where('fiscal_year', $fiscalYear)
            ->with(['proofs'])
            ->get();
            
        $hra = \App\Models\EmployeeHraDeclaration::where('employee_id', $employee->id)
            ->where('fiscal_year', $fiscalYear)
            ->first();

        $regime = EmployeeTaxRegime::firstOrCreate(
            ['employee_id' => $employee->id, 'fiscal_year' => $fiscalYear],
            ['regime' => 'New']
        );

        $data = [
            'sections' => $sections,
            'declarations' => $declarations,
            'hra' => $hra,
            'regime' => $regime,
            'fiscal_year' => $fiscalYear,
            'locked' => $regime->locked_at ? true : false,
            'config' => [
                'submission_window_open' => true,
                'hra_metro_limit' => 0.50,
                'hra_non_metro_limit' => 0.40
            ]
        ];

        // If it's a browser page load/refresh OR an Inertia navigation, return the page
        if (!request()->ajax() || request()->header('X-Inertia')) {
            return Inertia::render('Employee/Tax/Index', array_merge($data, [
                'employee' => $employee
            ]));
        }

        // Return raw JSON for axios background calls
        return response()->json($data);
    }

    /**
     * Update Tax Regime (Old/New)
     */
    /**
     * Update Tax Regime (Old/New) & Previous Employment
     */
    public function updateRegime(Request $request)
    {
        $request->validate([
            'regime' => 'required|in:Old,New',
            'fiscal_year' => 'required|string',
            'previous_gross_income' => 'nullable|numeric|min:0',
            'previous_tds_paid' => 'nullable|numeric|min:0',
            'previous_pf_deducted' => 'nullable|numeric|min:0',
            'previous_pt_paid' => 'nullable|numeric|min:0',
        ]);

        $employee = auth()->user()->employee;
        
        // Prevent if locked
        $existing = EmployeeTaxRegime::where('employee_id', $employee->id)
            ->where('fiscal_year', $request->fiscal_year)
            ->first();
            
        if ($existing && $existing->locked_at) {
            return response()->json(['message' => 'Tax details are locked by Admin'], 403);
        }

        EmployeeTaxRegime::updateOrCreate(
            ['employee_id' => $employee->id, 'fiscal_year' => $request->fiscal_year],
            [
                'regime' => $request->regime,
                'previous_gross_income' => $request->previous_gross_income ?? 0,
                'previous_tds_paid' => $request->previous_tds_paid ?? 0,
                'previous_pf_deducted' => $request->previous_pf_deducted ?? 0,
                'previous_pt_paid' => $request->previous_pt_paid ?? 0,
            ]
        );

        return response()->json(['message' => 'Tax Planner Updated']);
    }

    /**
     * Save HRA Declaration
     */
    public function storeHra(Request $request)
    {
        $request->validate([
             'fiscal_year' => 'required|string',
             'rent_monthly' => 'required|numeric|min:0',
             'landlord_name' => 'required|string',
             'landlord_pan' => 'nullable|string', 
             'rented_address' => 'required|string',
             'is_metro_city' => 'required|boolean'
        ]);
        
        // Compliance Check: Rent Limit
        $hraLimit = \App\Models\SystemSetting::where('group', 'tax')->where('key', 'rent_receipt_limit')->value('value') ?? 8333;
        
        if ($request->rent_monthly > $hraLimit && empty($request->landlord_pan)) {
            return response()->json([
                'message' => 'Validation Failed',
                'errors' => ['landlord_pan' => ["Landlord PAN is mandatory if monthly rent exceeds ₹{$hraLimit}"]]
            ], 422);
        }
        
        $employee = auth()->user()->employee;
        
        // Check Lock
        $regime = EmployeeTaxRegime::where('employee_id', $employee->id)
            ->where('fiscal_year', $request->fiscal_year)
            ->first();

        if ($regime && $regime->locked_at) {
            return response()->json(['message' => 'Tax settings are locked'], 403);
        }
        
        \App\Models\EmployeeHraDeclaration::updateOrCreate(
            ['employee_id' => $employee->id, 'fiscal_year' => $request->fiscal_year],
            [
                'rent_monthly' => $request->rent_monthly,
                'landlord_name' => $request->landlord_name,
                'landlord_pan' => $request->landlord_pan,
                'rented_address' => $request->rented_address,
                'is_metro_city' => $request->is_metro_city,
                'status' => 'Submitted'
            ]
        );
        
        return response()->json(['message' => 'HRA Details Saved']);
    }

    /**
     * Save Declarations (80C, 80D etc)
     */
    public function storeDeclarations(Request $request)
    {
        $request->validate([
            'fiscal_year' => 'required|string',
            'declarations' => 'array',
            'declarations.*.tax_section_id' => 'required|exists:tax_sections,id',
            'declarations.*.declared_amount' => 'required|numeric|min:0',
        ]);

        $employee = auth()->user()->employee;
        $fiscalYear = $request->fiscal_year;

        // Check Lock
        $regime = EmployeeTaxRegime::where('employee_id', $employee->id)
            ->where('fiscal_year', $fiscalYear)
            ->first();

        if ($regime && $regime->locked_at) {
            return response()->json(['message' => 'Declarations are locked for this fiscal year'], 403);
        }

        DB::transaction(function() use ($request, $employee, $fiscalYear) {
            foreach($request->declarations as $decl) {
                // Determine status - usually 'Submitted' upon save
                if ($decl['declared_amount'] >= 0) {
                    TaxDeclaration::updateOrCreate(
                        [
                            'employee_id' => $employee->id,
                            'fiscal_year' => $fiscalYear,
                            'tax_section_id' => $decl['tax_section_id']
                        ],
                        [
                            'declared_amount' => $decl['declared_amount'],
                            'status' => 'Submitted' 
                        ]
                    );
                }
            }
        });

        return response()->json(['message' => 'Declarations Saved Successfully']);
    }

    /**
     * Upload Proof File
     */
    public function uploadProof(Request $request)
    {
        $request->validate([
            'section_code' => 'nullable|string', // Can be 'HRA' or tax_section_id logic
            'tax_section_id' => 'required_without:section_code', // Flexible
            'fiscal_year' => 'required|string',
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120' // 5MB
        ]);

        $employee = auth()->user()->employee;
        $fiscalYear = $request->fiscal_year;
        
        // HRA Logic Special Case
        if ($request->section_code === 'HRA') {
             // We can store HRA proofs in a special TaxDeclaration or attach to EmployeeHraDeclaration
             // To keep it unified, let's find/create the '10(13A)' section declaration
             $hraSection = TaxSection::where('section_code', '10(13A)')->first();
             if (!$hraSection) return response()->json(['message' => 'HRA Section not configured'], 500);
             
             $declaration = TaxDeclaration::firstOrCreate(
                [
                    'employee_id' => $employee->id,
                    'fiscal_year' => $fiscalYear,
                    'tax_section_id' => $hraSection->id
                ],
                ['declared_amount' => 0, 'status' => 'Pending']
            );
        } else {
             // Standard Section
             $declaration = TaxDeclaration::firstOrCreate(
                [
                    'employee_id' => $employee->id,
                    'fiscal_year' => $fiscalYear,
                    'tax_section_id' => $request->tax_section_id
                ],
                ['declared_amount' => 0, 'status' => 'Pending']
            );
        }

        // Store File
        $path = $request->file('file')->store('tax_proofs/'.$fiscalYear.'/'.$employee->id, 'public');

        $proof = $declaration->proofs()->create([
            'file_path' => $path,
            'file_name' => $request->file('file')->getClientOriginalName()
        ]);

        return response()->json([
            'message' => 'Proof Uploaded',
            'proof' => $proof,
            'declaration_id' => $declaration->id
        ]);
    }

    /**
     * Delete Proof
     */
    public function deleteProof(InvestmentProof $proof)
    {
        // Ownership Check
        if ($proof->declaration->employee_id !== auth()->user()->employee?->id) {
             return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        // Lock check
        $regime = EmployeeTaxRegime::where('employee_id', $proof->declaration->employee_id)
            ->where('fiscal_year', $proof->declaration->fiscal_year)
            ->first();
            
        if ($regime && $regime->locked_at) {
             return response()->json(['message' => 'Locked'], 403);
        }

        Storage::disk('public')->delete($proof->file_path);
        $proof->delete();

        return response()->json(['message' => 'Proof Deleted']);
    }

    /**
     * Raise Dispute
     */
    public function raiseDispute(Request $request)
    {
        $request->validate([
            'id' => 'required',
            'type' => 'required|in:Investment,HRA',
            'reason' => 'required|string|min:5'
        ]);

        $employee = auth()->user()->employee;

        if ($request->type === 'Investment') {
            $decl = TaxDeclaration::where('employee_id', $employee->id)->findOrFail($request->id);
            $decl->update([
                'is_disputed' => true,
                'dispute_reason' => $request->reason,
                'status' => 'Submitted' // Revisit queue? Or keep Rejected but disputed?
                // Better keep status as is or make a custom status?
                // Re-submitting to queue makes sense if they want Admin to see it "Active" again.
                // But Admin filters by Dispute. Let's keep status same or Submitted.
                // If we set to Submitted, it appears in "Submitted" Filter.
                // If we set to Rejected + is_disputed = true, Admin needs to filter by "Disputed".
                // Let's set to 'Submitted' so it reappears in the main flow for attention.
                // Actually, let's keep it simple: Status => Submitted.
            ]);
            $decl->status = 'Submitted'; 
            $decl->save();
        } else {
             $decl = \App\Models\EmployeeHraDeclaration::where('employee_id', $employee->id)->findOrFail($request->id);
             $decl->update([
                'is_disputed' => true,
                'dispute_reason' => $request->reason,
                'status' => 'Submitted'
            ]);
        }

        return response()->json(['message' => 'Query Raised. Admin will review.']);
    }
}
