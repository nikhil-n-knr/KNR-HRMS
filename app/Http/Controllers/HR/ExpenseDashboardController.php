<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ExpenseDashboardController extends Controller
{
    /**
     * Main Dashboard View with Tabbed Data.
     */
    public function index(Request $request)
    {
        return Inertia::render('HR/Expenses/Dashboard', [
            'initialTab' => $request->query('tab', 'analytics'),
            'categories' => \App\Models\ExpenseCategory::all(),
            'workflows' => \App\Models\Workflow::where('module', 'EXPENSE')->get()
        ]);
    }

    /**
     * Tab A: Analytics & Insights.
     */
    public function stats(Request $request)
    {
        $currentMonth = now();
        $lastMonth = now()->subMonth();

        $stats = [
            'total_claims_this_month' => Expense::whereMonth('incurred_date', $currentMonth->month)
                ->whereYear('incurred_date', $currentMonth->year)
                ->sum('amount'),
            
            'total_claims_last_month' => Expense::whereMonth('incurred_date', $lastMonth->month)
                ->whereYear('incurred_date', $lastMonth->year)
                ->sum('amount'),

            'total_rejected_amount' => Expense::where('status', 'Rejected')
                ->whereYear('created_at', $currentMonth->year)
                ->sum('amount'),

            'outstanding_liability' => Expense::where('status', 'Approved')
                ->whereNull('reimbursed_on')
                ->sum('amount'),
            
            // Pie Chart Data: Category Split
            'category_split' => Expense::select('expense_category_id', DB::raw('sum(amount) as total'))
                ->whereYear('incurred_date', $currentMonth->year)
                ->groupBy('expense_category_id')
                ->with('category')
                ->get()
                ->map(fn($item) => [
                    'name' => $item->category->name ?? 'Unknown',
                    'value' => $item->total
                ]),

            // Operational Metric: Mean Claim Time (Created -> Reimbursed) (Approx for Paids)
            'avg_claim_days' => DB::table('expenses')
                ->whereNotNull('reimbursed_on')
                ->select(DB::raw('AVG(DATEDIFF(reimbursed_on, created_at)) as avg_days'))
                ->value('avg_days') ?? 0,
        ];
        
        // Aging Analysis (Overdue > 30 days)
        $aging = Expense::with('employee')
            ->where('status', 'Approved')
            ->whereNull('reimbursed_on')
            ->where('created_at', '<', now()->subDays(30))
            ->take(10)
            ->get();

        return response()->json([
            'metrics' => $stats,
            'overdue' => $aging
        ]);
    }

    /**
     * Tab B: Pending Approvals (Manager/Admin View Aggregation).
     */
    public function approvals(Request $request)
    {
        // For HR Admin, show ALL Pending or specific filters
        // Or if this is purely Manager view, filtering by team.
        // Assuming HR Super Admin sees everything or uses this to audit.
        
        $query = Expense::with(['employee', 'category', 'project'])
            ->whereIn('status', ['Pending', 'Processing']);
            
        if ($request->search) {
             $query->whereHas('employee', function($q) use ($request) {
                 $q->where('first_name', 'like', "%{$request->search}%")
                   ->orWhere('last_name', 'like', "%{$request->search}%");
             });
        }

        $approvals = $query->orderBy('created_at', 'asc')->paginate(15);
        
        return response()->json($approvals);
    }

    /**
     * Tab C: Ready for Disbursement.
     */
    public function disbursement(Request $request)
    {
        $query = Expense::with(['employee', 'category'])
            ->whereIn('status', ['Approved', 'Approved_Payroll'])
            ->whereNull('reimbursed_on');

        if ($request->search) {
             $query->whereHas('employee', function($q) use ($request) {
                 $q->where('first_name', 'like', "%{$request->search}%")
                   ->orWhere('last_name', 'like', "%{$request->search}%");
             });
        }
        
        // Filter by Payout Mode if needed
        if ($request->payout_mode) {
             $query->where('payout_method', $request->payout_mode);
        }

        $items = $query->orderBy('updated_at', 'asc')->paginate(15);

        return response()->json($items);
    }

    /**
     * Tab D: History.
     */
    public function history(Request $request)
    {
        $query = Expense::with(['employee', 'category'])
            ->whereIn('status', ['Paid', 'Rejected']); // Completed statuses

        if ($request->search) {
             $query->whereHas('employee', function($q) use ($request) {
                 $q->where('first_name', 'like', "%{$request->search}%")
                   ->orWhere('last_name', 'like', "%{$request->search}%");
             });
        }
        
        if ($request->date_from) $query->whereDate('incurred_date', '>=', $request->date_from);
        if ($request->date_to) $query->whereDate('incurred_date', '<=', $request->date_to);
        
        if ($request->min_amount) $query->where('amount', '>=', $request->min_amount);
        if ($request->max_amount) $query->where('amount', '<=', $request->max_amount);

        $history = $query->latest('updated_at')->paginate(20);

        return response()->json($history);
    }
}
