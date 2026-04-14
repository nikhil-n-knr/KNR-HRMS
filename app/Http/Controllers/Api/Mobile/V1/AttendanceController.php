<?php

namespace App\Http\Controllers\Api\Mobile\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Attendance\AttendanceRegistryService;
use App\Models\AttendanceLog;
use App\Services\Infrastructure\LoggerService;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    protected $attendanceService;

    public function __construct(AttendanceRegistryService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    /**
     * Get Today's Attendance Summary
     */
    public function index(Request $request)
    {
        $employee = $request->user()->employee;
        if (!$employee) {
            return response()->json(['message' => 'Employee profile not found.'], 404);
        }

        $today = Carbon::today();
        $log = AttendanceLog::with('sessions')
            ->where('employee_id', $employee->id)
            ->where('date', $today)
            ->first();

        $shift = $this->attendanceService->getShiftForDate($employee, $today);

        return response()->json([
            'log' => $log,
            'shift' => [
                'name' => $shift->name,
                'start_time' => $shift->start_time,
                'end_time' => $shift->end_time,
                'is_wfh_allowed' => $shift->allow_wfh ?? false,
            ],
            'is_clocked_in' => $log ? $log->sessions()->whereNull('out_time')->exists() : false
        ]);
    }

    /**
     * Mobile Clock In
     */
    public function clockIn(Request $request)
    {
        $request->validate([
            'lat' => 'nullable|numeric',
            'long' => 'nullable|numeric',
        ]);

        $employee = $request->user()->employee;
        if (!$employee) {
            return response()->json(['message' => 'Employee profile not found.'], 404);
        }

        try {
            $log = $this->attendanceService->clockIn(
                $employee, 
                $request->ip(), 
                'Mobile', 
                $request->lat, 
                $request->long
            );

            \Log::context(['user_id' => $request->user()->id, 'action' => 'mobile_clock_in']);
            LoggerService::info('Mobile Clock In Successful', [
                'lat' => $request->lat,
                'long' => $request->long,
                'ip' => $request->ip()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Clocked in successfully.',
                'log' => $log->load('sessions')
            ]);
        } catch (\Exception $e) {
            LoggerService::warning('Mobile Clock In Failed', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Mobile Clock Out
     */
    public function clockOut(Request $request)
    {
        $request->validate([
            'lat' => 'nullable|numeric',
            'long' => 'nullable|numeric',
        ]);

        $employee = $request->user()->employee;
        if (!$employee) {
            return response()->json(['message' => 'Employee profile not found.'], 404);
        }

        try {
            $log = $this->attendanceService->clockOut(
                $employee, 
                $request->ip(), 
                $request->lat, 
                $request->long
            );

            \Log::context(['user_id' => $request->user()->id, 'action' => 'mobile_clock_out']);
            LoggerService::info('Mobile Clock Out Successful', [
                'lat' => $request->lat,
                'long' => $request->long,
                'ip' => $request->ip()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Clocked out successfully.',
                'log' => $log->load('sessions')
            ]);
        } catch (\Exception $e) {
            LoggerService::warning('Mobile Clock Out Failed', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }
    /**
     * Unified Sync Protocol
     */
    public function sync(Request $request)
    {
        $request->validate([
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'action' => 'required|in:clock_in,clock_out'
        ]);

        return $request->action === 'clock_in' 
            ? $this->clockIn(new Request([...$request->all(), 'long' => $request->lng]))
            : $this->clockOut(new Request([...$request->all(), 'long' => $request->lng]));
    }
}
