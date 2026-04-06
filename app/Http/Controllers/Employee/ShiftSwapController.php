<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\ShiftSwap;
use App\Models\Employee;
use App\Models\Shift;
use App\Services\Infrastructure\LoggerService;
use Carbon\Carbon;

class ShiftSwapController extends Controller
{
    protected $logger;

    public function __construct(LoggerService $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Show Shift Swaps Dashboard.
     */
    public function index(Request $request)
    {
        $employee = auth()->user()->employee;

        if (!$employee) {
             if ($request->wantsJson()) return response()->json(['error' => 'No Employee Profile found.'], 404);
             return Inertia::render('Employee/Attendance/ShiftSwaps', [
                'outgoing' => [],
                'incoming' => [],
                'colleagues' => [],
                'shifts' => [],
                'my_shift_id' => null,
                'error' => 'No Employee Profile found.'
            ]);
        }

        // 1. My Outgoing Requests
        $outgoing = ShiftSwap::with(['recipient.user', 'shiftFrom', 'shiftTo'])
            ->where('requester_id', $employee->id)
            ->orderBy('date', 'desc')
            ->get();

        // 2. Incoming Requests (To me)
        $incoming = ShiftSwap::with(['requester.user', 'shiftFrom', 'shiftTo'])
            ->where('recipient_id', $employee->id)
            ->whereIn('status', ['Pending', 'Requested'])
            ->orderBy('date', 'asc')
            ->get();

        // 3. Colleagues (for dropdown)
        $colleagues = Employee::where('id', '!=', $employee->id)
            ->select('id', 'first_name', 'last_name', 'employee_code')
            ->with(['rotations' => function($q) {
                $q->where('start_date', '<=', now())->orderBy('start_date', 'desc')->limit(1);
            }])
            ->get()
            ->map(function($c) {
                $c->current_shift_id = $c->rotations->first()?->shift_id;
                unset($c->rotations);
                return $c;
            });
        
        // 4. My Current Shift
        $employee->load(['rotations' => function($q) {
            $q->where('start_date', '<=', now())->orderBy('start_date', 'desc')->limit(1);
        }]);
        $myShiftId = $employee->rotations->first()?->shift_id ?? \App\Models\Shift::where('is_default', true)->value('id');
        
        $shifts = \App\Models\Shift::select('id', 'name')->get();

        if ($request->wantsJson()) {
            return response()->json([
                'outgoing' => $outgoing,
                'incoming' => $incoming,
                'colleagues' => $colleagues,
                'shifts' => $shifts,
                'my_shift_id' => $myShiftId
            ]);
        }

        return Inertia::render('Employee/Attendance/ShiftSwaps', [
            'outgoing' => $outgoing,
            'incoming' => $incoming,
            'colleagues' => $colleagues,
            'shifts' => $shifts,
            'my_shift_id' => $myShiftId
        ]);
    }

    /**
     * Request a Shift Swap.
     */
    public function store(Request $request)
    {
        $request->validate([
            'recipient_id' => 'required|exists:employees,id',
            'date' => 'required|date|after:today',
        ]);

        $requester = auth()->user()->employee;
        if (!$requester) {
            if ($request->wantsJson()) return response()->json(['message' => 'No Employee Profile'], 404);
            return back()->with('error', 'No Employee Profile');
        }
        $recipient = Employee::find($request->recipient_id);
        $date = Carbon::parse($request->date);

        // 1. Identify Shifts (Mock logic for now as Rosters are TODO)
        // In real app, use AttendanceRegistryService->getShiftForDate()
        // Here we assume Default vs Default or mock.
        // We need to fetch the shifts to store them.
        $myShift = Shift::where('is_default', true)->first() ?? Shift::first(); 
        $theirShift = Shift::where('is_default', true)->first() ?? Shift::first();

        if (!$myShift || !$theirShift) {
             if ($request->wantsJson()) return response()->json(['message' => 'No shifts defined in system.'], 422);
             return back()->with('error', 'No shifts defined in system.');
        }

        // 2. Validation: Duplicate
        $exists = ShiftSwap::where('requester_id', $requester->id)
            ->where('date', $date)
            ->where('status', 'Pending')
            ->exists();
        
        if ($exists) {
            if ($request->wantsJson()) return response()->json(['message' => 'You already have a pending swap request for this date.'], 422);
            return back()->with('error', 'You already have a pending swap request for this date.');
        }

        // 3. Create Request
        $swap = ShiftSwap::create([
            'requester_id' => $requester->id,
            'recipient_id' => $recipient->id,
            'shift_id_from' => $myShift->id,
            'shift_id_to' => $theirShift->id,
            'date' => $date,
            'status' => 'Pending'
        ]);

        // Initialize Workflow
        // Note: For Shift Swaps, the first step might be "Peer Approval" which is handled by 'update' method (Accept/Reject).
        // Only AFTER Peer Accepts, should the Manager Workflow start?
        // OR the Workflow can be "Peer -> Manager".
        // If Peer -> Manager, then we init here.
        // But the current logic (Lines 177-183) handles Peer Accept manually.
        // Let's keep Peer Accept manual (it's unique) and start Manager Workflow AFTER Peer Accepts.
        // So NO change here? 
        // Wait, if I want full workflow, I should model Peer as a stage. 
        // But Peer is "Specific User" (Recipient). Workflow supports that.
        // Let's try to make it full workflow: Stage 1 = Peer (Recipient), Stage 2 = Manager.
        // BUT Recipient varies per request!
        // Workflow Engine supports `resolveApprover` but expects a predefined logic (Manager, Role).
        // It doesn't easily support "The user ID in recipient_id column".
        // SO: Keep Peer Accept manual, and trigger Manager Workflow in `update` method.
        
        $this->logger->log('attendance', 'create', "Requested shift swap with {$recipient->first_name} for {$date->toDateString()}", $requester->user_id);

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Shift swap requested.', 'swap' => $swap], 201);
        }

        return back()->with('success', 'Shift swap requested.');
    }

    /**
     * Accept or Reject a Request (As Recipient).
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'action' => 'required|in:accept,reject'
        ]);

        $employee = auth()->user()->employee;
        if (!$employee) {
             if ($request->wantsJson()) return response()->json(['message' => 'No Employee Profile'], 403);
             return back()->with('error', 'No Employee Profile');
        }
        $swap = ShiftSwap::findOrFail($id);

        // Security Check
        if ($swap->recipient_id !== $employee->id) {
            if ($request->wantsJson()) return response()->json(['message' => 'Unauthorized'], 403);
            return back()->with('error', 'Unauthorized');
        }

        if ($request->action === 'accept') {
            // Peer Accepted. Now moves to Manager Approval.
            $swap->update(['status' => 'Accepted']);
            $this->logger->log('attendance', 'update', "Accepted shift swap request #{$swap->id}", $employee->user_id);

            // Initialize Manager Workflow
            // Ensure a 'shift_swap' workflow exists targeting the Requester's manager? Or Recipient's?
            // Usually Shift Swap requires approval from the Requester's Manager.
            // We pass the Requester's User object to init.
            if ($swap->requester && $swap->requester->user) {
                // We resolve WorkflowService needed
                $workflowService = app(\App\Services\WorkflowService::class);
                $workflowService->initializeWorkflow('shift_swap', $swap->id, $swap->requester->user);
            }
            
            if ($request->wantsJson()) return response()->json(['message' => 'Swap Accepted! Awaiting Manager Approval.']);
            return back()->with('success', 'Swap Accepted! Awaiting Manager Approval.');
        } else {
            $swap->update(['status' => 'Rejected']);
            if ($request->wantsJson()) return response()->json(['message' => 'Swap Rejected.']);
            return back()->with('success', 'Swap Rejected.');
        }
    }
}
