<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\AttendanceRegularization;
use App\Models\OvertimeRequest;
use App\Models\WfhRequest;
use App\Models\ShiftSwap;
use Illuminate\Support\Facades\DB;

class RequestController extends Controller
{
    /**
     * Handle the Employee Request Hub.
     * Supports tabs: swaps, overtime, wfh, floating, regularization.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        if (!$user->employee) {
            return Inertia::render('Employee/Requests/Index', [
                'tab' => $request->input('tab', 'regularization'),
                'requests' => ['data' => []],
                'error' => 'Your account is not linked to an Employee Profile. Please contact HR.'
            ]);
        }

        $tab = $request->input('tab', 'regularization'); // Default to regularization or swaps
        $requests = null;

        // Common Filters (if applicable to all)
        // Note: Specific filters (month, year) should be applied in switch

        switch ($tab) {
            case 'leaves':
                $requests = \App\Models\LeaveRequest::where('employee_id', $user->employee->id)
                    ->with('leaveType')
                    ->latest()
                    ->paginate(15);
                break;

            case 'swaps':
                $requests = ShiftSwap::where(function($q) use ($user) {
                        $q->where('requester_id', $user->employee->id)
                          ->orWhere('recipient_id', $user->employee->id);
                    })
                    ->with(['requester', 'recipient', 'shiftFrom', 'shiftTo'])
                    ->latest()
                    ->paginate(15);
                break;

            case 'overtime':
                $requests = OvertimeRequest::where('employee_id', $user->employee->id)
                    ->latest()
                    ->paginate(15);
                break;

            case 'wfh':
                $requests = WfhRequest::where('employee_id', $user->employee->id)
                    ->latest()
                    ->paginate(15);
                break;

            case 'floating':
                // Assuming FloatingHolidayRequest model exists or similar logic
                // For now returning empty paginator or implementation if model known
                // Previous check showed 'FloatingHolidayController' exists in Admin.
                $requests = \App\Models\FloatingHolidayRequest::where('user_id', $user->id)
                    ->with('holiday')
                    ->latest()
                    ->paginate(15);
                break;
                
            case 'regularization':
            default:
                $requests = AttendanceRegularization::where('employee_id', $user->employee->id)
                    ->latest()
                    ->paginate(15);
                $tab = 'regularization'; // Enforce valid tab
                break;
        }

        $leaveTypes = \App\Models\LeaveType::where('is_active', true)->select('id', 'name')->get();

        return Inertia::render('Employee/Requests/Index', [
            'tab' => $tab,
            'requests' => $requests,
            'filters' => $request->all(),
            'leaveTypes' => $leaveTypes,
            // Pass options if needed for forms (e.g. Swaps need eligible employees?)
            // 'swapOptions' => ...
        ]);
    }
}
