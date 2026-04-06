<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeDocument;
use App\Models\EmployeeFamily;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class EmployeeReportController extends Controller
{
    /**
     * Display a global report of all employee documents.
     */
    public function documents(Request $request)
    {
        $query = EmployeeDocument::with('employee.department', 'employee.location')
            ->latest();

        if ($request->filled('search')) {
            $query->whereHas('employee', function ($q) use ($request) {
                $q->where('first_name', 'like', '%' . $request->search . '%')
                  ->orWhere('last_name', 'like', '%' . $request->search . '%')
                  ->orWhere('employee_code', 'like', '%' . $request->search . '%');
            })->orWhere('title', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        $documents = $query->paginate(20)->withQueryString();

        return Inertia::render('Admin/Employee/Reports/Documents', [
            'documents' => $documents,
            'filters' => $request->only(['search', 'category']),
            'categories' => EmployeeDocument::select('category')->distinct()->pluck('category')
        ]);
    }

    /**
     * Display a global report of all employee family members.
     */
    public function family(Request $request)
    {
        $query = EmployeeFamily::with('employee.department', 'employee.location')
            ->latest();

        if ($request->filled('search')) {
            $query->whereHas('employee', function ($q) use ($request) {
                $q->where('first_name', 'like', '%' . $request->search . '%')
                  ->orWhere('last_name', 'like', '%' . $request->search . '%')
                  ->orWhere('employee_code', 'like', '%' . $request->search . '%');
            })->orWhere('name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('relationship')) {
            $query->where('relationship', $request->relationship);
        }

        $families = $query->paginate(20)->withQueryString();

        return Inertia::render('Admin/Employee/Reports/Family', [
            'families' => $families,
            'filters' => $request->only(['search', 'relationship']),
            'relationships' => ['spouse', 'child', 'father', 'mother', 'sibling', 'other']
        ]);
    }

    /**
     * Display a global report of employee history/activity.
     */
    public function history(Request $request)
    {
        $query = DB::table('activity_logs')
            ->where('subject_type', 'like', '%Employee%')
            ->orderByDesc('created_at');

        if ($request->filled('search')) {
            $query->where('description', 'like', '%' . $request->search . '%');
        }

        $history = $query->paginate(20)->withQueryString();

        // Transform to include causer info (simplified for now)
        $history->getCollection()->transform(function ($log) {
            $causer = $log->causer_id ? \App\Models\User::find($log->causer_id) : null;
            return [
                'id' => $log->id,
                'description' => $log->description,
                'subject_id' => $log->subject_id,
                'causer_name' => $causer ? $causer->name : 'System',
                'created_at' => $log->created_at,
                'properties' => json_decode($log->properties, true)
            ];
        });

        return Inertia::render('Admin/Employee/Reports/History', [
            'history' => $history,
            'filters' => $request->only(['search'])
        ]);
    }
}
