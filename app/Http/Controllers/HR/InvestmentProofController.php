<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\InvestmentProof;
use App\Models\TaxDeclaration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class InvestmentProofController extends Controller
{
    /**
     * Store (Upload) a Proof
     */
    public function store(Request $request)
    {
        $request->validate([
            'tax_declaration_id' => 'required|exists:tax_declarations,id',
            'file' => 'required|file|mimes:pdf,jpg,png,jpeg|max:5120', // 5MB
            'description' => 'nullable|string'
        ]);

        $declaration = TaxDeclaration::findOrFail($request->tax_declaration_id);
        
        // Security: Ensure upload is for own declaration
        if ($declaration->employee_id !== auth()->user()->employee_id) {
            abort(403);
        }

        $path = $request->file('file')->store('investment_proofs', 'public');

        InvestmentProof::create([
            'tax_declaration_id' => $declaration->id,
            'employee_id' => $declaration->employee_id,
            'file_path' => $path,
            'original_name' => $request->file('file')->getClientOriginalName(),
            'status' => 'Pending'
        ]);

        return back()->with('success', 'Proof uploaded successfully.');
    }

    /**
     * Admin: Verify or Reject Proof
     */
    public function verify(Request $request, InvestmentProof $proof)
    {
        // Permission Check
        if (!auth()->user()->hasRole(['Super Admin', 'Admin', 'HR'])) {
            abort(403);
        }

        $request->validate([
            'action' => 'required|in:approve,reject',
            'verified_amount' => 'required_if:action,approve|numeric|min:0',
            'rejection_reason' => 'required_if:action,reject|string|nullable'
        ]);

        if ($request->action === 'approve') {
            $proof->update([
                'status' => 'Approved',
                'verified_amount' => $request->verified_amount,
                'verified_by' => auth()->id(),
                'rejection_reason' => null
            ]);

            // Update Parent Declaration Verified Amount
            // Currently, we'll sum up approved proofs for this declaration? 
            // OR if this proof covers the whole declaration, we update the declaration directly.
            // Let's assume simplest: 1 Declaration = Multiple Proofs. sum(verified_amount) -> declaration.verified_amount
            
            $totalVerified = InvestmentProof::where('tax_declaration_id', $proof->tax_declaration_id)
                ->where('status', 'Approved')
                ->sum('verified_amount');
                
            $proof->declaration->update([
                'verified_amount' => $totalVerified,
                'status' => 'Verified' // Mark partially or fully verified
            ]);
            
            return back()->with('success', 'Proof approved & amount updated.');

        } else {
            $proof->update([
                'status' => 'Rejected',
                'rejection_reason' => $request->rejection_reason,
                'verified_by' => auth()->id(),
                'verified_amount' => 0
            ]);
            
            // Should we update declaration status? Maybe 'Action Required'?
             $proof->declaration->update(['status' => 'Action Required']);
             
             return back()->with('success', 'Proof rejected.');
        }
    }
    
    public function destroy(InvestmentProof $proof)
    {
        if ($proof->employee_id !== auth()->user()->employee_id && !auth()->user()->hasRole(['Admin', 'HR'])) {
            abort(403);
        }
        
        if ($proof->status === 'Approved') {
            return back()->with('error', 'Cannot delete verified proof.');
        }

        Storage::disk('public')->delete($proof->file_path);
        $proof->delete();

        return back()->with('success', 'Proof deleted.');
    }

    /**
     * Admin Verification Queue List
     */
    public function index()
    {
         if (!auth()->user()->hasRole(['Super Admin', 'Admin', 'HR'])) {
            abort(403);
        }
        
        // Fetch current active fiscal year (simplified for now to current year context)
        // Ideally should support FY switching.
        $year = now()->year;
        $fiscalYear = (now()->month > 3) ? "$year-".($year+1) : ($year-1)."-$year";

        $proofs = InvestmentProof::with(['declaration.section', 'uploader.user'])
            ->whereHas('declaration', function($q) use ($fiscalYear) {
                $q->where('fiscal_year', $fiscalYear);
            })
            ->latest()
            ->paginate(20);

        return Inertia::render('HR/Tax/Verification/Index', [
            'proofs' => $proofs,
            'fiscal_year' => $fiscalYear
        ]);
    }
}
