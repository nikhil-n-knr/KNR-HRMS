<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\FloatingHolidayRequest;
use App\Models\FloatingHolidayAllocation; // Assuming this model exists
use Illuminate\Support\Facades\Response;

use App\Services\Infrastructure\LoggerService;

class FloatingHolidayController extends Controller
{
    protected $logger;

    public function __construct(LoggerService $logger)
    {
        $this->logger = $logger;
    }

    public function index(Request $request)
    {
        $query = FloatingHolidayRequest::with(['user.employee.department', 'holiday']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user.employee', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%");
            });
        }
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return Inertia::render('Admin/Attendance/FloatingHolidayList', [
            'requests' => $query->latest()->paginate(20)->withQueryString(),
            'filters' => $request->only(['search', 'status']),
            'employees' => \App\Models\Employee::select('id', 'first_name', 'last_name')->get()->map(function($e) {
                return ['id' => $e->id, 'name' => $e->first_name . ' ' . $e->last_name];
            }),
            'holidays' => \App\Models\Holiday::where('type', 'Restricted')->orderBy('date')->get(['id', 'name', 'date']),
            'departments' => \App\Models\Department::select('id', 'name')->get(),
            'locations' => \App\Models\Location::select('id', 'name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'holiday_id' => 'required|exists:holidays,id',
            'status' => 'required|in:Pending,Approved,Rejected',
        ]);

        // Map employee_id to user_id as FloatingHolidayRequest uses user_id
        $employee = \App\Models\Employee::find($validated['employee_id']);
        if (!$employee->user_id) {
             return redirect()->back()->with('error', 'Selected employee does not have a linked user account.');
        }

        $req = FloatingHolidayRequest::create([
            'user_id' => $employee->user_id,
            'holiday_id' => $validated['holiday_id'],
            'status' => $validated['status'],
            'approved_by' => auth()->id()
        ]);

        $this->logger->log('Attendance', 'AssignFloating', "Assigned holiday to employee", [
            'request_id' => $req->id,
            'employee_id' => $employee->id,
            'holiday_id' => $validated['holiday_id']
        ]);

        return redirect()->back()->with('success', 'Floating holiday assigned successfully.');
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
             'status' => 'required|in:Pending,Approved,Rejected',
        ]);

        $req = FloatingHolidayRequest::findOrFail($id);
        $oldStatus = $req->status;
        
        $req->update([
            'status' => $validated['status'],
            'approved_by' => auth()->id()
        ]);

        $this->logger->log('Attendance', 'UpdateFloating', "Updated status to {$validated['status']}", [
            'request_id' => $req->id,
            'old_status' => $oldStatus,
            'new_status' => $validated['status']
        ]);

        return redirect()->back()->with('success', 'Request updated successfully.');
    }

    public function destroy($id)
    {
        $req = FloatingHolidayRequest::findOrFail($id);
        $empId = $req->user_id; // actually user_id, mapped to employee often
        $req->delete();

        $this->logger->log('Attendance', 'DeleteFloating', "Deleted floating holiday request", [
            'request_id' => $id,
            'assigned_user_id' => $empId
        ]);

        return redirect()->back()->with('success', 'Request deleted successfully.');
    }

    public function export(Request $request)
    {
         $query = FloatingHolidayRequest::with(['user.employee.department', 'holiday']);
         
         if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

         $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="floating_holidays.csv"',
        ];

        $callback = function () use ($query) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Requested Date', 'Holiday Name', 'Employee', 'Department', 'Status']);

            $query->chunk(100, function ($rows) use ($file) {
                foreach ($rows as $row) {
                    fputcsv($file, [
                        $row->holiday->date,
                        $row->holiday->name,
                        $row->user->employee->first_name . ' ' . $row->user->employee->last_name,
                        $row->user->employee->department->name ?? '-',
                        $row->status
                    ]);
                }
            });
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
