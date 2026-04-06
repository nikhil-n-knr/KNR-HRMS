<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\AttendanceRegularization;
use App\Models\AttendanceLog;
use App\Services\Infrastructure\LoggerService;
use Carbon\Carbon;

class RegularizationController extends Controller
{
    protected $logger;

    public function __construct(LoggerService $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Submit a Regularization Request.
     */
    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date|before_or_equal:today',
            'reason' => 'required|string|max:255',
            'regularized_in_time' => 'required|date_format:H:i',
            'regularized_out_time' => 'required|date_format:H:i|after:regularized_in_time',
        ]);

        $employee = auth()->user()->employee;
        $date = Carbon::parse($request->date);

        // 1. Check Duplicate
        $exists = AttendanceRegularization::where('employee_id', $employee->id)
            ->where('date', $date)
            ->where('status', 'Pending')
            ->exists();

        if ($exists) {
            return back()->with('error', 'You already have a pending request for this date.');
        }

        // 2. Create Request
        $reg = AttendanceRegularization::create([
            'employee_id' => $employee->id,
            'date' => $date,
            'reason' => $request->reason,
            'regularized_in_time' => $request->regularized_in_time,
            'regularized_out_time' => $request->regularized_out_time,
            'status' => 'Pending'
        ]);

        // 3. Log
        $this->logger->log('attendance', 'create', "Requested regularization for {$date->toDateString()}", $employee->user_id);

        // 4. Trigger Workflow
        try {
            $workflowService = app(\App\Services\WorkflowService::class);
            $instance = $workflowService->initializeWorkflow('attendance_regularization', $reg->id, auth()->user());

            if (!$instance) {
                // Auto-approve if no workflow configured
                $reg->update(['status' => 'Approved']);
                return back()->with('success', 'Regularization approved automatically (No workflow configured).');
            }
        } catch (\Exception $e) {
            \Log::error('Regularization Workflow Error: ' . $e->getMessage());
        }

        return back()->with('success', 'Regularization request submitted for approval.');
    }
}
