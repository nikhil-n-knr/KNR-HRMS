<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{User, Employee, LeaveRequest, LeaveType, AttendanceRegularization, ShiftSwap, Shift, Expense, ExpenseCategory, Timesheet, OvertimeRequest, WfhRequest, FloatingHolidayRequest, Payroll, Holiday, Workflow, WorkflowInstance, WorkflowApproval};
use App\Services\WorkflowService;
use Illuminate\Support\Str;

class WorkflowDemoSeeder extends Seeder
{
    protected $workflowService;

    public function __construct()
    {
        $this->workflowService = app(WorkflowService::class);
    }

    public function run()
    {
        $initiatorUser = User::where('email', 'admin@example.com')->first() ?? User::first();
        if (!$initiatorUser) {
            echo "No user found to seed.\n";
            return;
        }
        $initiatorEmployee = $initiatorUser->employee ?? Employee::first();
        
        $approverUser = User::where('id', '!=', $initiatorUser->id)->first() ?? $initiatorUser;

        // Ensure Dependencies
        $leaveType = LeaveType::first() ?? LeaveType::create(['name' => 'Annual Leave', 'days_per_year' => 20]);
        $expenseCat = ExpenseCategory::first() ?? ExpenseCategory::create(['name' => 'Travel']);
        $holiday = Holiday::first() ?? Holiday::create(['name' => 'New Year', 'date' => now()->format('Y-01-01'), 'type' => 'public']);

        $shiftId = Shift::first()?->id ?? 1;

        $modules = [
            'leave_request' => function($state) use ($initiatorEmployee, $leaveType) {
                return LeaveRequest::create([
                    'uuid' => Str::uuid(),
                    'employee_id' => $initiatorEmployee->id,
                    'leave_type_id' => $leaveType->id,
                    'start_date' => now()->addDays(rand(1, 30)),
                    'end_date' => now()->addDays(rand(31, 40)),
                    'total_days' => 3,
                    'reason' => "Vacation $state",
                    'status' => 'pending'
                ]);
            },
            'attendance_regularization' => function($state) use ($initiatorEmployee) {
                return AttendanceRegularization::create([
                    'employee_id' => $initiatorEmployee->id,
                    'date' => now()->subDays(rand(1, 25)), // rand to avoid unique constraint if re-run
                    'reason' => "Forgot punch $state",
                    'regularized_in_time' => '09:00:00',
                    'regularized_out_time' => '18:00:00',
                    'status' => 'pending'
                ]);
            },
            'shift_swap' => function($state) use ($initiatorEmployee, $shiftId) {
                return ShiftSwap::create([
                    'requester_id' => $initiatorEmployee->id,
                    'recipient_id' => Employee::where('id', '!=', $initiatorEmployee->id)->first()?->id ?? $initiatorEmployee->id,
                    'shift_id_from' => $shiftId,
                    'shift_id_to' => $shiftId,
                    'date' => now()->addDays(rand(1, 15)),
                    'status' => 'Pending'
                ]);
            },
            'expense' => function($state) use ($initiatorEmployee, $expenseCat) {
                return Expense::create([
                    'title' => "Expense $state",
                    'employee_id' => $initiatorEmployee->id,
                    'incurred_date' => now()->subDays(rand(1, 30)),
                    'expense_category_id' => $expenseCat->id,
                    'category' => $expenseCat->name,
                    'currency' => 'INR',
                    'amount' => rand(500, 5000),
                    'status' => 'Pending'
                ]);
            },
            'timesheet' => function($state) use ($initiatorEmployee) {
                return Timesheet::create([
                    'employee_id' => $initiatorEmployee->id,
                    'date' => now()->subDays(rand(1, 15)),
                    'task_description' => "Dev work $state",
                    'hours_spent' => 8,
                    'status' => 'Submitted'
                ]);
            },
            'overtime_request' => function($state) use ($initiatorEmployee) {
                return OvertimeRequest::create([
                    'employee_id' => $initiatorEmployee->id,
                    'date' => now()->subDays(rand(1, 10)),
                    'minutes' => rand(60, 240),
                    'reason' => "Overtime $state",
                    'status' => 'pending'
                ]);
            },
            'wfh_request' => function($state) use ($initiatorEmployee) {
                return WfhRequest::create([
                    'employee_id' => $initiatorEmployee->id,
                    'date' => now()->addDays(rand(1, 10)),
                    'reason' => "WFH $state",
                    'status' => 'Pending'
                ]);
            },
            'floating_holiday_request' => function($state) use ($initiatorUser, $holiday) {
                return FloatingHolidayRequest::create([
                    'user_id' => $initiatorUser->id,
                    'holiday_id' => $holiday->id,
                    'status' => 'Requested'
                ]);
            },
            'payroll' => function($state) use ($initiatorUser) {
                return Payroll::create([
                    'month' => rand(1, 12),
                    'year' => date('Y'),
                    'start_date' => now()->startOfMonth(),
                    'end_date' => now()->endOfMonth(),
                    'status' => 'Draft',
                    'batch_name' => "Batch $state " . Str::random(5),
                    'total_payout' => rand(100000, 900000),
                    'processed_by' => $initiatorUser->id
                ]);
            }
        ];

        foreach ($modules as $type => $createFn) {
            echo "Seeding $type...\n";
            
            try {
                // 1. Pending
                $entity = $createFn('pending');
                $instance = $this->workflowService->initializeWorkflow($type, $entity->id, $initiatorUser);
                
                // 2. Approved
                $entityApp = $createFn('approved');
                $instanceApp = $this->workflowService->initializeWorkflow($type, $entityApp->id, $initiatorUser);
                if ($instanceApp) {
                    $this->approveAllStages($instanceApp);
                }

                // 3. Rejected
                $entityRej = $createFn('rejected');
                $instanceRej = $this->workflowService->initializeWorkflow($type, $entityRej->id, $initiatorUser);
                if ($instanceRej) {
                    $approval = $instanceRej->approvals()->where('status', 'pending')->first();
                    if ($approval) {
                        $this->workflowService->reject($approval, $approval->approver, 'Duplicate request');
                    }
                }
            } catch (\Exception $e) {
                echo "Skipping one state of $type due to: " . $e->getMessage() . "\n";
            }
        }
    }

    protected function approveAllStages($instance)
    {
        while ($instance->status === 'pending') {
            $approval = $instance->approvals()->where('status', 'pending')->first();
            if (!$approval) break;
            
            $this->workflowService->approve($approval, $approval->approver, 'Approved by seeder');
            $instance->refresh();
        }
    }
}
