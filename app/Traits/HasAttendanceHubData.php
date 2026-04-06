<?php

namespace App\Traits;

use App\Models\AttendancePolicy;
use App\Models\Department;
use App\Models\Location;
use App\Models\Role;
use App\Models\User;
use App\Models\Team;
use App\Models\Workflow;
use App\Models\PointRule;
use App\Models\Badge;
use App\Models\Shift;
use App\Models\BiometricDevice;
use App\Models\AttendanceZone;
use App\Models\Employee;
use Inertia\Inertia;

trait HasAttendanceHubData
{
    /**
     * Get the base props for the Attendance Hub.
     */
    protected function getHubBaseData($tab, $tenantId = null)
    {
        $tenantId = $tenantId ?? auth()->user()->tenant_id;
        
        $loadPolicies = fn() => [
            'global' => AttendancePolicy::where('tenant_id', $tenantId)
                ->where('name', 'Global Attendance Rules')
                ->first() ?? AttendancePolicy::where('tenant_id', $tenantId)->orderBy('priority', 'asc')->first(),
            'list' => AttendancePolicy::withCount(['employees', 'departments'])
                ->where('tenant_id', $tenantId)
                ->where('name', '!=', 'Global Attendance Rules')
                ->orderBy('priority', 'desc')
                ->get()
        ];

        return [
            'tab' => $tab,
            'locations' => Location::where('tenant_id', $tenantId)->select('id', 'name')->get(),
            'departments' => Department::withCount('employees')->get(),
            
            // System Data - Lazy
            'policy' => Inertia::lazy(fn() => $loadPolicies()['global']),
            'policies' => Inertia::lazy(fn() => $loadPolicies()['list']),
            'rules' => Inertia::lazy(fn() => PointRule::all()),
            'badges' => Inertia::lazy(fn() => Badge::all()),
            'shifts' => Inertia::lazy(fn() => Shift::where('tenant_id', $tenantId)->get()),
            'teams' => Inertia::lazy(fn() => Team::with(['manager:id,name', 'parent:id,name', 'members:id,name,email,team_id'])->withCount('members')->get()),
            'workflows' => Inertia::lazy(fn() => Workflow::with(['stages', 'stages.role'])->where('tenant_id', $tenantId)->get()),
            'roles' => Inertia::lazy(fn() => Role::where('tenant_id', $tenantId)->get()),
            'users' => Inertia::lazy(fn() => User::where('tenant_id', $tenantId)->select('id', 'name')->get()),
            'biometric_devices' => Inertia::lazy(fn() => BiometricDevice::with('zone')->where('tenant_id', $tenantId)->get()),
            'attendance_zones' => Inertia::lazy(fn() => AttendanceZone::where('tenant_id', $tenantId)->get()),
            'employees' => Inertia::lazy(fn() => Employee::where('tenant_id', $tenantId)->select('id', 'first_name', 'last_name', 'employee_code')->get()),
        ];
    }
}
