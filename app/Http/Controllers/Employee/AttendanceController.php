<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Services\Attendance\AttendanceRegistryService;
use App\Models\AttendanceLog;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    protected $registry;

    public function __construct(AttendanceRegistryService $registry)
    {
        $this->registry = $registry;
    }

    /**
     * Display the Attendance Dashboard.
     */
    /**
     * Display the Attendance Dashboard.
     */
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        $employee = $user->employee;

        $data = [
            'todayLog' => null,
            'history' => [],
            'currentShift' => null,
            'error' => null
        ];

        if (!$employee) {
            $data['error'] = 'Your user account is not linked to an Employee Profile. Please contact HR.';
        } else {
            $today = Carbon::today();
            
            // Fetch today's log
            $todayLog = AttendanceLog::with('sessions')
                ->where('employee_id', $employee->id)
                ->where('date', $today)
                ->first();

            // Fetch monthly history (paginated)
            $history = AttendanceLog::where('employee_id', $employee->id)
                ->whereMonth('date', $today->month)
                ->orderBy('date', 'desc')
                ->get();

            $data['todayLog'] = $todayLog;
            $data['history'] = $history;
            $data['currentShift'] = $todayLog ? $todayLog->shift : $this->registry->getShiftForDate($employee, $today);
        }

        if ($request->wantsJson()) {
            return response()->json($data);
        }

        // Inject tab prop for Hub
        $data['tab'] = 'dashboard';
        return Inertia::render('Employee/Attendance/Hub', $data);
    }

    /**
     * Clock In Action.
     */
    /**
     * Clock In Action.
     */
    public function clockIn(Request $request)
    {
        $employee = auth()->user()->employee;
        
        try {
            $this->registry->clockIn($employee, $request->ip());

            if ($request->wantsJson()) {
                return response()->json(['message' => 'Clocked In Successfully marked as Present', 'success' => true]);
            }
            return back()->with('success', 'Clocked In Successfully marked as Present');
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(['message' => $e->getMessage(), 'success' => false], 422);
            }
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Clock Out Action.
     */
    public function clockOut(Request $request)
    {
        $employee = auth()->user()->employee;

        try {
            $this->registry->clockOut($employee, $request->ip());
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Clocked Out Successfully', 'success' => true]);
            }
            return back()->with('success', 'Clocked Out Successfully');
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(['message' => $e->getMessage(), 'success' => false], 422);
            }
            return back()->with('error', $e->getMessage());
        }
    }
}
