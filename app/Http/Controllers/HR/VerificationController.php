<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\TaxDeclaration;
use App\Models\EmployeeHraDeclaration;
use Illuminate\Http\Request;
use Inertia\Inertia;

class VerificationController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status', 'Submitted');
        
        // standard declarations
        $declarations = TaxDeclaration::with(['employee', 'section', 'proofs'])
            ->where('status', $status)
            ->whereHas('employee', function($q) {
                $q->where('status', 'active');
            })
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($d) {
                return [
                    'id' => $d->id,
                    'type' => 'Investment', // vs HRA
                    'employee_name' => $d->employee->name ?? 'Unknown',
                    'employee_code' => $d->employee->employee_code,
                    'section' => $d->section->name,
                    'section_code' => $d->section->section_code,
                    'claimed' => $d->declared_amount,
                    'verified' => $d->verified_amount,
                    'status' => $d->status,
                    'proofs' => $d->proofs,
                    'is_disputed' => $d->is_disputed,
                    'dispute_reason' => $d->dispute_reason,
                    'created_at' => $d->created_at,
                    'remarks' => $d->remarks
                ];
            });

        // HRA declarations
        $hra = EmployeeHraDeclaration::with(['employee'])
            ->where('status', $status)
             ->whereHas('employee', function($q) {
                $q->where('status', 'active');
            })
            ->get()
            ->map(function ($h) {
                // Fetch proofs linked to HRA (10(13A))
                // Optimally we should eager load proofs via a relation if possible, or fetch separate
                $proofs = \App\Models\InvestmentProof::whereHas('declaration', function($q) use ($h) {
                    $q->where('employee_id', $h->employee_id)
                      ->where('fiscal_year', $h->fiscal_year)
                      ->whereHas('section', fn($sq) => $sq->where('section_code', '10(13A)'));
                })->get();

                return [
                    'id' => $h->id,
                    'type' => 'HRA',
                    'employee_name' => $h->employee->name ?? 'Unknown',
                    'employee_code' => $h->employee->employee_code,
                    'section' => 'House Rent Allowance',
                    'section_code' => '10(13A)',
                    'claimed' => $h->rent_monthly * 12, // Annualized roughly or usually they declare annual
                    // Wait, DB stores monthly. Let's assume claimed is annual for verification context or display monthly.
                    // Let's display Monthly Rent
                    'claimed_display' => $h->rent_monthly . ' / month',
                    'verified' => null, // HRA usually doesn't store verified amount in same way? Or we update master data?
                    // For simply, we verify the document. HRA calculation is auto.
                    // But maybe we need to verify the *exemption*? No, we verify the *Rent Paid*.
                    // Lets imply verified means "Approved"
                    'status' => $h->status,
                    'proofs' => $proofs,
                    'is_disputed' => $h->is_disputed,
                    'dispute_reason' => $h->dispute_reason,
                    'created_at' => $h->created_at,
                    'remarks' => $h->rejection_reason
                ];
            });

        $queue = $declarations->concat($hra)->sortByDesc('created_at')->values();

        return Inertia::render('HR/Tax/VerificationQueue', [
            'queue' => $queue,
            'filters' => ['status' => $status]
        ]);
    }

    public function verify(Request $request)
    {
        $request->validate([
            'id' => 'required',
            'type' => 'required|in:Investment,HRA',
            'verified_amount' => 'nullable|numeric|min:0',
            'remarks' => 'nullable|string'
        ]);

        if ($request->type === 'Investment') {
            $decl = TaxDeclaration::findOrFail($request->id);
            $decl->update([
                'verified_amount' => $request->verified_amount ?? $decl->declared_amount,
                'status' => 'Verified',
                'remarks' => $request->remarks
            ]);
            
            // Recalculate TDS
            app(\App\Services\Payroll\TaxCalculatorService::class)->recalculateTds($decl->employee_id, auth()->id());
            
        } else {
            $decl = EmployeeHraDeclaration::findOrFail($request->id);
            // HRA verification usually just accepts the rent amount
            $decl->update([
                'status' => 'Verified',
                'rejection_reason' => $request->remarks
            ]);
            
            // Recalculate TDS
            app(\App\Services\Payroll\TaxCalculatorService::class)->recalculateTds($decl->employee_id, auth()->id());
        }

        return back()->with('success', 'Declaration Verified & Tax Recalculated');
    }

    public function reject(Request $request)
    {
        $request->validate([
            'id' => 'required',
            'type' => 'required|in:Investment,HRA',
            'reason' => 'required|string'
        ]);

        if ($request->type === 'Investment') {
            TaxDeclaration::where('id', $request->id)->update([
                'status' => 'Rejected',
                'remarks' => $request->reason,
                'verified_amount' => 0
            ]);
        } else {
             EmployeeHraDeclaration::where('id', $request->id)->update([
                'status' => 'Rejected',
                'rejection_reason' => $request->reason
            ]);
        }

        return back()->with('success', 'Declaration Rejected');
    }
}
