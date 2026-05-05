<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class ExpenseSettlementController extends Controller
{
    /**
     * Display approved expenses for settlement.
     */
    public function index(Request $request)
    {
        $query = Expense::with(['employee' => function($q) { $q->withTrashed(); }, 'category', 'project'])
            ->whereIn('status', ['Approved', 'Approved_Payroll']) // List Approved and those waiting for Payroll
            ->whereNull('reimbursed_on'); // Not yet fully settled/paid

        if ($request->filled('search')) {
            $query->whereHas('employee', function($q) use ($request) {
                $q->withTrashed()
                  ->where(function($sq) use ($request) {
                      $sq->where('first_name', 'like', "%{$request->search}%")
                         ->orWhere('last_name', 'like', "%{$request->search}%");
                  });
            });
        }

        if ($request->filled('payout_method')) {
            $query->where('payout_method', $request->payout_method);
        }

        $expenses = $query->orderBy('incurred_date', 'desc')->paginate(20)->withQueryString();

        return Inertia::render('Admin/Finance/ExpenseSettlement', [
            'expenses' => $expenses,
            'filters' => $request->only(['search', 'payout_method'])
        ]);
    }

    /**
     * Bulk Action: Mark selected expenses for Payroll.
     */
    public function markForPayroll(Request $request)
    {
        $request->validate([
            'ids' => 'required|array', 
            'ids.*' => 'exists:expenses,id',
            'target_month' => 'required|date_format:Y-m' // Expects "2026-02"
        ]);

        $targetDate = \Carbon\Carbon::createFromFormat('Y-m', $request->target_month)->startOfMonth();

        Expense::whereIn('id', $request->ids)->update([
            'payout_method' => 'payroll',
            'status' => 'Approved_Payroll', 
            'reimbursed_on' => $targetDate
        ]);

        return redirect()->back()->with('success', 'Selected expenses scheduled for ' . $targetDate->format('M Y') . ' Payroll.');
    }

    /**
     * Bulk Action: Settle Manually (Direct Payment).
     */
    public function settleManually(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:expenses,id',
            'transaction_reference' => 'required|string',
            'settlement_date' => 'required|date'
        ]);

        DB::transaction(function () use ($request) {
            Expense::whereIn('id', $request->ids)->update([
                'status' => 'Paid',
                'payout_method' => 'direct',
                'transaction_reference' => $request->transaction_reference,
                'reimbursed_on' => $request->settlement_date,
                'settlement_date' => $request->settlement_date
            ]);
        });

        return redirect()->back()->with('success', 'Selected expenses marked as Paid.');
    }
}