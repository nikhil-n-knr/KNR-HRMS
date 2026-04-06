<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Employee;

class ManualEntryController extends Controller {

    public function index(Request $request) {
        $employees = Employee::select('id', 'first_name', 'last_name', 'employee_code')->limit(100)->get();

        return Inertia::render('Admin/Attendance/Hub', [
            'tab' => 'manual',
            'employees' => $employees
        ]);
    }
    
    public function store(Request $request) {
        // Placeholder for storing manual entry
        $request->validate([
             'employee_id' => 'required',
             'date' => 'required|date',
             'clock_in' => 'required',
             'clock_out' => 'required',
        ]);
        
        return redirect()->back()->with('success', 'Manual entry added (Simulated).');
    }
}
