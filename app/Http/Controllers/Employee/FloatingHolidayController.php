<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\FloatingHolidayAllocation;
use App\Models\FloatingHolidayRequest;
use App\Models\Holiday;
use App\Services\Infrastructure\LoggerService;
use Carbon\Carbon;

class FloatingHolidayController extends Controller
{
    protected $logger;

    public function __construct(LoggerService $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Show available Restricted Holidays and Quota.
     */
    public function index(Request $request) // Added Request injection
    {
        $user = auth()->user();
        $year = Carbon::now()->year;

        // 1. Get Quota & Calculate Used
        $allocation = FloatingHolidayAllocation::firstOrCreate(
            ['user_id' => $user->id, 'year' => $year],
            ['total_quota' => 2, 'used_count' => 0]
        );

        // Dynamic Calculation for data integrity
        $usedCount = FloatingHolidayRequest::where('user_id', $user->id)
            ->whereYear('created_at', $year) // or based on holiday date? Usually based on Holiday Date.
            ->whereHas('holiday', function($q) use ($year) {
                $q->whereYear('date', $year);
            })
            ->where('status', 'Approved')
            ->count();
            
        $allocation->used_count = $usedCount; 

        // 2. Get Available RH Options (Future only)
        // Level 5: Filter by Location? For now, 'All'.
        $upcomingRestrictedHolidays = Holiday::where('type', 'Restricted')
            ->whereDate('date', '>=', Carbon::now())
            ->whereYear('date', $year)
            ->orderBy('date', 'asc')
            ->get();

        // 3. Get My Requests for this year
        $myRequests = FloatingHolidayRequest::with('holiday')
            ->where('user_id', $user->id)
            ->whereHas('holiday', function($q) use ($year) {
                $q->whereYear('date', $year);
            })
            ->get();

        if ($request->wantsJson()) {
            return response()->json([
                'allocation' => $allocation,
                'availableHolidays' => $upcomingRestrictedHolidays,
                'myRequests' => $myRequests
            ]);
        }

        return Inertia::render('Employee/Attendance/FloatingHolidays', [
            'allocation' => $allocation,
            'availableHolidays' => $upcomingRestrictedHolidays,
            'myRequests' => $myRequests
        ]);
    }

    /**
     * Apply for a Restricted Holiday.
     */
    public function store(Request $request)
    {
        $request->validate([
            'holiday_id' => 'required|exists:holidays,id'
        ]);

        $user = auth()->user();
        $year = Carbon::now()->year;

        // 1. Validation: Quota Check
        $allocation = FloatingHolidayAllocation::firstOrCreate(
            ['user_id' => $user->id, 'year' => $year],
            ['total_quota' => 2, 'used_count' => 0]
        );

        $usedCount = FloatingHolidayRequest::where('user_id', $user->id)
            ->whereHas('holiday', function($q) use ($year) {
                $q->whereYear('date', $year);
            })
            ->where('status', 'Approved')
            ->count();

        if ($usedCount >= $allocation->total_quota) {
            if ($request->wantsJson()) return response()->json(['message' => 'You have exhausted your Restricted Holiday quota for this year.'], 422);
            return back()->with('error', 'You have exhausted your Restricted Holiday quota for this year.');
        }

        // 2. Validation: Duplicate Check
        $exists = FloatingHolidayRequest::where('user_id', $user->id)
            ->where('holiday_id', $request->holiday_id)
            ->exists();

        if ($exists) {
            if ($request->wantsJson()) return response()->json(['message' => 'You have already requested this holiday.'], 422);
            return back()->with('error', 'You have already requested this holiday.');
        }

        // 3. Validation: Date Check (Cannot apply for past)
        $holiday = Holiday::find($request->holiday_id);
        if ($holiday->date < Carbon::now()) {
            if ($request->wantsJson()) return response()->json(['message' => 'Cannot apply for a past holiday.'], 422);
            return back()->with('error', 'Cannot apply for a past holiday.');
        }

        // 4. Create Request
        $fhRequest = FloatingHolidayRequest::create([
            'user_id' => $user->id,
            'holiday_id' => $holiday->id,
            'status' => 'Requested' 
        ]);

        // Trigger Workflow
        try {
            $workflowService = app(\App\Services\WorkflowService::class);
            $instance = $workflowService->initializeWorkflow('floating_holiday', $fhRequest->id, auth()->user());

            if (!$instance) {
                // Auto-approve if no workflow configured
                $fhRequest->update(['status' => 'Approved']);
                $this->logger->log('attendance', 'floating_holiday_auto_approve', "Holiday #{$fhRequest->id} auto-approved (No workflow)", $user->id);
                return back()->with('success', 'Restricted Holiday approved automatically (No workflow configured).');
            }
        } catch (\Exception $e) {
            \Log::error('Floating Holiday Workflow Error: ' . $e->getMessage());
        }

        // 5. Log
        $this->logger->log('attendance', 'create', "Applied for Restricted Holiday: {$holiday->name}", $user->id);

        if ($request->wantsJson()) {
             return response()->json(['message' => 'Restricted Holiday requested successfully.', 'request' => $fhRequest], 201);
        }

        return back()->with('success', 'Restricted Holiday requested successfully. Pending approval.');
    }
}
