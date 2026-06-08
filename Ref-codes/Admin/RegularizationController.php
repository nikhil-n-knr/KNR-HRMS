<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\AttendanceRegularization;
use App\Models\AttendanceLog;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class RegularizationController extends Controller
{
    public function index(Request $request)
    {
        $query = AttendanceRegularization::with(['employee.department']);

        // Global Filter via Relation
        $query->whereHas('employee', function ($q) use ($request) {
            $q->applyStandardFilters($request);
        });

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->wantsJson()) {
            return response()->json($query->latest()->paginate(20));
        }

        return Inertia::render('Admin/Attendance/Hub', [
             'tab' => 'approvals',
             'requests' => $query->latest()->paginate(20)->withQueryString(),
             'departments' => \App\Models\Department::select('id', 'name')->get(),
             'locations' => \App\Models\Location::select('id', 'name')->get(),
             'filters' => $request->only(['search', 'department_id', 'location_id', 'status'])
        ]);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:Approved,Rejected',
            'remarks' => 'nullable|string|max:255'
        ]);

        $reg = AttendanceRegularization::findOrFail($id);

        if ($reg->status !== 'Pending') {
             return back()->with('error', 'Request already processed.');
        }

        DB::transaction(function () use ($reg, $validated) {
            $reg->status = $validated['status'];
            $reg->approver_id = auth()->id();
            $reg->approver_remarks = $validated['remarks'] ?? null;
            $reg->save();

            if ($validated['status'] === 'Approved') {
                // Update Attendance Log
                $log = AttendanceLog::firstOrCreate(
                    ['employee_id' => $reg->employee_id, 'date' => $reg->date],
                    ['status' => 'Absent'] // Default if missing
                );
                
                // Calculate minutes
                $in = Carbon::parse($reg->regularized_in_time);
                $out = Carbon::parse($reg->regularized_out_time);
                $minutes = $out->diffInMinutes($in);

                $log->update([
                    'status' => 'Present',
                    'is_regularized' => true,
                    'total_work_minutes' => $minutes,
                    // Clear late/early flags? Let's assume regularization fixes them.
                    'is_late' => false,
                    'is_half_day' => false, 
                    'late_minutes' => 0,
                    'early_leaving_minutes' => 0
                ]);
            }
        });

        return back()->with('success', "Request {$validated['status']}");
    }

    public function myList(Request $request)
    {
        $user = auth()->user();
        if (!$user->employee) {
            return Inertia::render('Employee/Requests/Index', [
                'requests' => ['data' => []],
                'error' => 'Your account is not linked to an Employee Profile. Please contact HR.'
            ]);
        }

        $requests = AttendanceRegularization::where('employee_id', $user->employee->id)
            ->latest()
            ->paginate(15);

        return Inertia::render('Employee/Requests/Index', [
            'requests' => $requests
        ]);
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $isAdmin = $user->roles->contains('name', 'Super Admin') || $user->roles->contains('name', 'Admin');

        $validated = $request->validate([
             'employee_id' => $isAdmin ? 'required|exists:employees,id' : 'nullable',
             'date' => 'required|date',
             'regularized_in_time' => 'required',
             'regularized_out_time' => 'required',
             'reason' => 'required|string',
        ]);

        if (!$isAdmin && !$user->employee) {
            return back()->with('error', 'Your account is not linked to an Employee Profile. Please contact HR.');
        }

        $employeeId = $isAdmin ? $validated['employee_id'] : $user->employee->id;
        $date = Carbon::parse($validated['date']);

        // Check Duplicate
        $exists = AttendanceRegularization::where('employee_id', $employeeId)
            ->where('date', $date->toDateString())
            ->where('status', 'Pending')
            ->exists();

        if ($exists) {
            return back()->with('error', 'A pending request already exists for this date.');
        }

        $status = $isAdmin ? 'Approved' : 'Pending';
        $approverId = $isAdmin ? $user->id : null;
        $remarks = $isAdmin ? 'Admin Created' : null;

        $reg = AttendanceRegularization::create([
            'employee_id' => $employeeId,
            'date' => $date->toDateString(),
            'regularized_in_time' => $validated['regularized_in_time'],
            'regularized_out_time' => $validated['regularized_out_time'],
            'reason' => $validated['reason'],
            'status' => $status,
            'approver_id' => $approverId,
            'approver_remarks' => $remarks
        ]);

        if ($status === 'Approved') {
            // Auto-update log
            $log = AttendanceLog::firstOrCreate(
                ['employee_id' => $reg->employee_id, 'date' => $reg->date],
                ['status' => 'Absent']
            );
            
            $in = Carbon::parse($reg->regularized_in_time);
            $out = Carbon::parse($reg->regularized_out_time);
            $minutes = $out->diffInMinutes($in);
    
            $log->update([
                'status' => 'Present',
                'is_regularized' => true,
                'total_work_minutes' => $minutes,
                'is_late' => false,
                'is_half_day' => false, 
                'late_minutes' => 0,
                'early_leaving_minutes' => 0
            ]);
        } else {
            // Trigger Workflow for employee
            try {
                $workflowService = app(\App\Services\WorkflowService::class);
                $instance = $workflowService->initializeWorkflow('attendance_regularization', $reg->id, $user);

                if (!$instance) {
                    // Auto-approve if no workflow configured
                    $reg->update(['status' => 'Approved']);
                    
                    $log = AttendanceLog::firstOrCreate(
                        ['employee_id' => $reg->employee_id, 'date' => $reg->date],
                        ['status' => 'Absent']
                    );
                    
                    $in = Carbon::parse($reg->regularized_in_time);
                    $out = Carbon::parse($reg->regularized_out_time);
                    $minutes = $out->diffInMinutes($in);
            
                    $log->update([
                        'status' => 'Present',
                        'is_regularized' => true,
                        'total_work_minutes' => $minutes,
                        'is_late' => false,
                        'is_half_day' => false, 
                        'late_minutes' => 0,
                        'early_leaving_minutes' => 0
                    ]);
                }
            } catch (\Exception $e) {
                \Log::error('Regularization Workflow Error: ' . $e->getMessage());
            }
        }

        return redirect()->back()->with('success', $isAdmin ? 'Regularization created and processed.' : 'Request submitted for approval.');
    } // End store

    public function export(Request $request)
    {
         $query = AttendanceRegularization::with(['employee']);
         
         $query->whereHas('employee', function ($q) use ($request) {
            $q->applyStandardFilters($request);
         });
         
         $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="regularization_audit.csv"',
        ];

        $callback = function () use ($query) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Date', 'Employee', 'Reason', 'Status']);

            $query->chunk(100, function ($rows) use ($file) {
                foreach ($rows as $row) {
                    fputcsv($file, [
                        $row->date,
                        $row->employee->first_name . ' ' . $row->employee->last_name,
                        $row->reason,
                        $row->status
                    ]);
                }
            });
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
