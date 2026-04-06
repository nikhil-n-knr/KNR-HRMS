<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Project;
use App\Services\Workflow\WorkflowService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ExpenseController extends Controller
{
    public function index()
    {
        $expenses = Expense::with(['category', 'currentStage', 'project', 'payroll'])
            ->where('employee_id', auth()->user()->employee_id)
            ->latest('incurred_date')
            ->paginate(15);
            
        $categories = ExpenseCategory::where('is_active', true)->get();
        $projects = Project::select('id', 'name', 'code')->get(); // Basic project options
        
        $stats = [
            'total_this_month' => Expense::where('status', 'Approved')->where('employee_id', auth()->user()->employee_id)->whereMonth('incurred_date', now()->month)->sum('amount'),
            'total_pending' => Expense::where('status', 'Pending')->where('employee_id', auth()->user()->employee_id)->count(),
        ];

        return Inertia::render('HR/Expenses/Index', [
            'expenses' => $expenses,
            'categories' => $categories,
            'projects' => $projects,
            'stats' => $stats
        ]);
    }

    /**
     * Store new expense claim.
     */
    public function store(Request $request, WorkflowService $workflowService)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'expense_category_id' => 'required|exists:expense_categories,id',
            'incurred_date' => 'required|date',
            'description' => 'nullable|string',
            'receipt' => 'nullable|file|mimes:jpeg,png,pdf|max:2048',
            'project_id' => 'nullable|exists:projects,id',
            'is_billable' => 'boolean',
            'payout_method' => 'nullable|in:payroll,direct'
        ]);

        // Check if category requires receipt
        $category = ExpenseCategory::find($request->expense_category_id);
        if ($category && $category->requires_bill_proof && !$request->hasFile('receipt')) {
            return back()->withErrors(['receipt' => 'Receipt is mandatory for this category.']);
        }

        $receiptPath = null;
        if ($request->hasFile('receipt')) {
            $receiptPath = $request->file('receipt')->store('expenses', 'public');
        }

        $employee = auth()->user()->employee;
        if (!$employee) {
            return back()->withErrors(['message' => 'No employee record linked to accounts.']);
        }

        $expense = Expense::create([
            'title' => $request->title,
            'amount' => $request->amount,
            'expense_category_id' => $request->expense_category_id,
            'incurred_date' => $request->incurred_date,
            'description' => $request->description,
            'status' => 'Pending', // Initial status
            'receipt_path' => $receiptPath,
            'employee_id' => $employee->id,
            'project_id' => $request->project_id,
            'is_billable' => $request->is_billable ?? false,
            'payout_method' => $request->payout_method ?? 'payroll'
        ]);
        
        // Initiate Workflow
        $workflowService->initiate($expense);

        return back()->with('success', 'Expense Claim Submitted')->setStatusCode(303);
    }
}
