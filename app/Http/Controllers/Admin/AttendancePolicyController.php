<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendancePolicy;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AttendancePolicyController extends Controller
{
    use \App\Traits\HasAttendanceHubData;

    public function index(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;
        $tab = $request->tab ?? 'policies';
        
        $data = $this->getHubBaseData($tab, $tenantId);

        // Tab Specific Data Sources
        
        // 1. Centralized Approval Requests (Regularization, Overtime, WFH)
        $requestModels = [
            'approvals' => \App\Models\AttendanceRegularization::class,
            'overtime' => \App\Models\OvertimeRequest::class,
            'wfh' => \App\Models\WfhRequest::class,
            'floating_requests' => \App\Models\FloatingHolidayRequest::class,
            'my_holidays' => \App\Models\FloatingHolidayRequest::class, // Or whatever the correct model is
        ];
        
        $activeModel = $requestModels[$tab] ?? \App\Models\AttendanceRegularization::class;
        $isRequestTab = isset($requestModels[$tab]);

        // Determine relation strings based on the model since FloatingHolidayRequest uses 'user' instead of 'employee'
        $relations = ($activeModel === \App\Models\FloatingHolidayRequest::class) 
            ? ['user.employee'] 
            : ['employee.department'];

        // Safest approach to eager loading for different models
        if ($activeModel === \App\Models\FloatingHolidayRequest::class) {
            $data['requests'] = $isRequestTab
                ? $activeModel::with(['user.employee', 'holiday'])->latest()->paginate(15)->withQueryString()
                : \Inertia\Inertia::lazy(fn() => $activeModel::with(['user.employee', 'holiday'])->latest()->paginate(15)->withQueryString());
        } else {
            $data['requests'] = $isRequestTab
                ? $activeModel::with(['employee.department'])->whereHas('employee', fn($q) => $q->where('tenant_id', $tenantId))->latest()->paginate(15)->withQueryString()
                : \Inertia\Inertia::lazy(fn() => $activeModel::with(['employee.department'])->whereHas('employee', fn($q) => $q->where('tenant_id', $tenantId))->latest()->paginate(15)->withQueryString());
        }

        // 1.1 Team Approvals (Managerial Context)
        if ($tab === 'team_approvals') {
            $data['leaves'] = \App\Models\LeaveRequest::with(['employee.department'])->latest()->limit(50)->get();
            $data['regularizations'] = \App\Models\AttendanceRegularization::with(['employee.department'])->latest()->limit(50)->get();
            $data['swaps'] = \App\Models\ShiftSwap::with(['requester', 'recipient', 'shiftFrom', 'shiftTo'])->latest()->limit(50)->get();
            $data['overtime'] = \App\Models\OvertimeRequest::with(['employee.department'])->latest()->limit(50)->get();
            $data['wfh'] = \App\Models\WfhRequest::with(['employee.department'])->latest()->limit(50)->get();
            $data['floatingHolidays'] = \App\Models\FloatingHolidayRequest::with(['user.employee'])->latest()->limit(50)->get();
            $data['all_pending'] = []; // Combined view if needed
        }

        // 2. Shift Swaps (Correcting relation names to match model)
        $data['swaps'] = ($tab === 'swap_requests')
            ? \App\Models\ShiftSwap::with(['requester', 'recipient', 'shiftFrom', 'shiftTo'])->whereHas('requester', fn($q) => $q->where('tenant_id', $tenantId))->latest()->paginate(15)->withQueryString()
            : \Inertia\Inertia::lazy(fn() => \App\Models\ShiftSwap::with(['requester', 'recipient', 'shiftFrom', 'shiftTo'])->whereHas('requester', fn($q) => $q->where('tenant_id', $tenantId))->latest()->paginate(15)->withQueryString());

        // 3. Config Props (Timesheets, Holidays)
        if ($tab === 'timesheets' || $tab === 'timesheet_view') {
            $data['projects'] = \App\Models\Project::where('tenant_id', $tenantId)->get();
        } else {
            $data['projects'] = \Inertia\Inertia::lazy(fn() => \App\Models\Project::where('tenant_id', $tenantId)->get());
        }

        if ($tab === 'holidays' || $tab === 'floating_requests') {
            $data['holidays'] = \App\Models\Holiday::where('tenant_id', $tenantId)->whereYear('date', now()->year)->get();
        } else {
            $data['holidays'] = \Inertia\Inertia::lazy(fn() => \App\Models\Holiday::where('tenant_id', $tenantId)->whereYear('date', now()->year)->get());
        }

        if ($request->wantsJson()) {
            return response()->json($data);
        }

        return Inertia::render('Admin/Attendance/Hub', $data);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'priority' => 'required|integer|min:0',
            'rules' => 'nullable|array',
            'late_mark_threshold' => 'nullable|integer|min:0',
            'deduction_rule' => 'nullable|array',
            'wfh_policy' => 'nullable|array',
            'overtime_policy' => 'nullable|array',
            'timesheet_policy' => 'nullable|array',
            'sandwich_rule_enabled' => 'boolean'
        ]);

        $validated['tenant_id'] = auth()->user()->tenant_id;

        // Logical Validation
        if (!empty($validated['timesheet_policy'])) {
             $min = $validated['timesheet_policy']['min_daily_hours'] ?? 0;
             $max = $validated['timesheet_policy']['max_daily_hours'] ?? 24;
             if ($min > $max) {
                 return back()->withErrors(['timesheet_policy' => 'Minimum hours cannot be greater than maximum hours.']);
             }
        }

        AttendancePolicy::create($validated);

        return redirect()->back()->with('success', 'Policy created successfully.');
    }

    public function update(Request $request, AttendancePolicy $policy)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'priority' => 'required|integer|min:0',
            'rules' => 'nullable|array',
            'late_mark_threshold' => 'nullable|integer|min:0',
            'deduction_rule' => 'nullable|array',
            'wfh_policy' => 'nullable|array',
            'overtime_policy' => 'nullable|array',
            'timesheet_policy' => 'nullable|array',
            'sandwich_rule_enabled' => 'boolean'
        ]);

        // Logical Validation
        if (!empty($validated['timesheet_policy'])) {
             $min = $validated['timesheet_policy']['min_daily_hours'] ?? 0;
             $max = $validated['timesheet_policy']['max_daily_hours'] ?? 24;
             if ($min > $max) {
                 return back()->withErrors(['timesheet_policy' => 'Minimum hours cannot be greater than maximum hours.']);
             }
        }

        \Log::info('Policy Update Success', ['id' => $policy->id]);

        $policy->update($validated);

        return to_route('admin.attendance.policies')->with('success', 'Policy updated successfully.')
            ->setStatusCode(303);
    }

    public function destroy(AttendancePolicy $policy)
    {
        $policy->delete();
        return redirect()->back()->with('success', 'Policy deleted.');
    }

    /**
     * Fallback for PUT requests where the ID might be missing from the URL.
     * Expects 'id' in the request body.
     */
    public function updateFallback(Request $request)
    {
        $id = $request->input('id');

        if (!$id) {
            return back()->withErrors(['error' => 'Policy ID is missing for update.']);
        }

        $policy = AttendancePolicy::findOrFail($id);
        
        return $this->update($request, $policy);
    }
}
