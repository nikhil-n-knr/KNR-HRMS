<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExpenseCategory;
use App\Models\Workflow;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ExpenseCategoryController extends Controller
{
    public function index()
    {
        $categories = ExpenseCategory::with('workflow')->orderBy('sort_order')->paginate(15);
        $workflows = Workflow::where('module', 'EXPENSE')->where('is_active', true)->get();
        // Master Data for Dropdowns
        $departments = \App\Models\Department::select('id', 'name')->get();
        // $grades = \App\Models\Grade::select('id', 'name')->get(); // If Grade model exists
        
        return Inertia::render('Admin/ExpenseCategories/Index', [
            'categories' => $categories,
            'workflows' => $workflows,
            'departments' => $departments
        ]);
    }
    
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'code' => 'nullable|string',
            'icon' => 'nullable|string',
            'description' => 'nullable|string',
            'workflow_id' => 'nullable|exists:workflows,id',
            'unit_type' => 'required|in:FIXED,MILEAGE,PER_DIEM',
            'unit_rate' => 'nullable|numeric',
            'requires_bill_proof' => 'boolean',
            'default_payout_method' => 'required|in:payroll,direct',
            'limits' => 'nullable|array',
            'rules' => 'nullable|array',
            'visibility' => 'nullable|array',
            'is_active' => 'boolean'
        ]);
        
        ExpenseCategory::create($data);
        
        return back()->with('success', 'Category Created');
    }
    
    public function update(Request $request, ExpenseCategory $category)
    {
         $data = $request->validate([
            'name' => 'required|string',
            'code' => 'nullable|string',
            'icon' => 'nullable|string',
            'description' => 'nullable|string',
            'workflow_id' => 'nullable|exists:workflows,id',
            'unit_type' => 'required|in:FIXED,MILEAGE,PER_DIEM',
            'unit_rate' => 'nullable|numeric',
            'requires_bill_proof' => 'boolean',
            'limits' => 'nullable|array',
            'rules' => 'nullable|array',
            'visibility' => 'nullable|array',
            'is_active' => 'boolean'
        ]);
        
        $category->update($data);
        
        return back()->with('success', 'Category Updated');
    }
    
    public function destroy(ExpenseCategory $category)
    {
        // Check if used?
        $category->delete();
        return back()->with('success', 'Category Deleted');
    }
}
