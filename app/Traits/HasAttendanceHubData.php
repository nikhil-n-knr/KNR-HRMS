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
            
            // System Data - Standard Closures (Inertia will evaluate these)
            'policy' => fn() => $loadPolicies()['global'],
            'policies' => fn() => $loadPolicies()['list'],
            'rules' => fn() => PointRule::all(),
            'badges' => fn() => Badge::all(),
            'shifts' => fn() => Shift::where('tenant_id', $tenantId)->get(),
            'teams' => fn() => Team::with(['manager:id,name', 'parent:id,name', 'role:id,name', 'members:id,name,email,team_id'])->withCount('members')->get(),
            'workflows' => fn() => Workflow::with(['stages', 'stages.role', 'stages.user', 'stages.team', 'stages.department'])->where('tenant_id', $tenantId)->orderBy('created_at', 'desc')->get(),
            'roles' => fn() => Role::where('tenant_id', $tenantId)->get(),
            'users' => fn() => User::where('tenant_id', $tenantId)->select('id', 'name')->get(),
            'biometric_devices' => fn() => BiometricDevice::with('zone')->where('tenant_id', $tenantId)->get(),
            'attendance_zones' => fn() => AttendanceZone::where('tenant_id', $tenantId)->get(),
            'employees' => fn() => Employee::where('tenant_id', $tenantId)->select('id', 'user_id', 'first_name', 'last_name', 'employee_code')->get(),
        ];
    }
}
