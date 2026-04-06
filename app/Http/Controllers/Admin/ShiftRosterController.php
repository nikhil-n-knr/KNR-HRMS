<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Employee;
use App\Models\Shift;
use App\Models\AttendanceLog; // For specific day overrides (Optional)
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ShiftRosterController extends Controller
{
    public function view(Request $request)
    {
        return $this->index($request); 
    }

    public function index(Request $request)
    {
        // 1. Fetch Date Range (Default: Current Month)
        // 1. Fetch Date Range (Max 45 Days)
        $start = Carbon::parse($request->input('start', Carbon::now()->startOfMonth()->toDateString()));
        $end = Carbon::parse($request->input('end', Carbon::now()->endOfMonth()->toDateString()));

        if ($start->diffInDays($end) > 45) {
            $end = $start->copy()->addDays(45);
        }

        $startStr = $start->toDateString();
        $endStr = $end->toDateString();
        
        // 2. Fetch Employees (Standard Filters)
        $query = Employee::with('department')->applyStandardFilters($request);

        $employees = $query->paginate(50);

        // 3. Fetch Assigned Shifts for this range
        // Note: In MVP, shifts are often "Default" or stored on User.
        // For Roster, we need a daily mapping. We can use `attendance_logs` logic or a dedicated `shift_assignments` table.
        // To simplify Phase 2: We will assume we are setting specific overrides or defaults.
        
        // Simulating usage of Daily Attendance Logs as "Plan" placeholders if they don't exist
        // Real implementation: specialized `schedule` table.
        
        // Fetch Shifts - Filtered by Location if selected
        $shiftsQuery = Shift::query();
        if ($request->filled('location_id')) {
            // Shifts that are Global (null location_ids) OR contain the specific location_id
            $locId = $request->location_id;
            $shiftsQuery->where(function($q) use ($locId) {
                $q->whereNull('location_ids')
                  ->orWhereJsonContains('location_ids', $locId)
                  ->orWhereJsonContains('location_ids', (string)$locId); // Handle string/int casting issues
            });
        }
        $shifts = $shiftsQuery->get();

        // 3. Fetch Assigned Shifts (From 'shift_rosters' table)
        // This is the Phase 3 Enterprise Roster Source of Truth
        $rosterEntries = \App\Models\ShiftRoster::whereIn('employee_id', $employees->pluck('id'))
                    ->whereBetween('date', [$start, $end])
                    ->select('employee_id', 'date', 'shift_id')
                    ->get();

        $rosterData = [];
        foreach ($rosterEntries as $entry) {
            // date cast in model might return Carbon object, ensure string key
            $d = $entry->date instanceof \Carbon\Carbon ? $entry->date->toDateString() : $entry->date;
            $rosterData[$entry->employee_id][$d] = $entry->shift_id;
        }

        if ($request->wantsJson() || $request->has('json')) {
            return response()->json([
                'employees' => $employees,
                'shifts' => $shifts,
                'start' => $startStr,
                'end' => $endStr,
                'roster' => $rosterData
            ]);
        }

        // Just in case, if it's an AJAX call but header missing, we might want to return JSON?
        // But for Hub tab loading, we rely on axios.
        // Let's add a fallback check for 'X-Requested-With' just to be safe, 
        // though wantsJson() (checking Accept: application/json) is standard.
        if ($request->ajax()) {
             return response()->json([
                'employees' => $employees,
                'shifts' => $shifts,
                'start' => $startStr,
                'end' => $endStr,
                'roster' => $rosterData
            ]);
        }

        return Inertia::render('Admin/Attendance/Hub', [
            'tab' => 'roster',
            'shifts' => $shifts,
            'start' => $startStr,
            'end' => $endStr,
            'locations' => \App\Models\Location::select('id', 'name')->get(),
            'departments' => \App\Models\Department::select('id', 'name')->get(),
        ]);
    }

    /**
     * Batch Assign Shifts for a range of dates.
     */
    public function assign(Request $request)
    {
        $request->validate([
            'employee_ids' => 'required|array',
            'shift_id' => 'required|exists:shifts,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $employeeIds = $request->employee_ids;
        $shiftId = $request->shift_id;
        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);
        
        DB::transaction(function () use ($employeeIds, $shiftId, $startDate, $endDate) {
            $period = $startDate->daysUntil($endDate);
            
            foreach ($employeeIds as $empId) {
                foreach ($period as $date) {
                    // Update/Create Roster Entry
                    \App\Models\ShiftRoster::updateOrCreate(
                        ['employee_id' => $empId, 'date' => $date->toDateString()],
                        [
                            'shift_id' => $shiftId,
                            'is_published' => true,
                            'assigned_by' => auth()->id(),
                            'notes' => 'Bulk Assignment'
                        ]
                    );
                }
            }
        });

        return response()->json(['message' => 'Shifts assigned successfully']);
    }
}
