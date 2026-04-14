<?php

use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\LocationController;
use App\Http\Controllers\Admin\RegularizationController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Employee\TimesheetController; // Import this
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Webhook\GitWebhookController;

Route::post('/webhooks/devops/{provider}', [GitWebhookController::class, 'handle']);

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('/webhooks/git/{provider?}', [\App\Http\Controllers\Webhook\GitWebhookController::class, 'handle']);
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    // Load permissions and employee record
    $user = $request->user()->load(['roles.permissions', 'employee']);
    
    // Check for Super Admin
    $isSuperAdmin = $user->roles->contains('name', 'Super Admin');

    $permissions = [];
    if ($isSuperAdmin) {
        $permissions = ['*']; 
    } else {
        // Merge permissions from ALL roles
        $permissions = $user->roles->flatMap(function($role) {
            return $role->permissions->map(function($p) {
                return "{$p->module}.{$p->submodule}.{$p->action}";
            });
        })->unique()->values()->toArray();
    }

    $userData = $user->toArray();
    
    // Compatibility: Frontend expects `user.role`
    $primaryRole = $user->roles->first();
    $userData['role'] = $primaryRole ? $primaryRole->toArray() : null;

    return array_merge($userData, [
        'capabilities' => $permissions,
        'is_super_admin' => $isSuperAdmin
    ]);
});

Route::middleware(['auth:sanctum'])->prefix('admin')->name('api.admin.')->group(function () {
    // Role Management
    Route::get('roles/matrix', [RoleController::class, 'matrix'])->name('roles.matrix');
    Route::apiResource('roles', RoleController::class);

    // Timesheets (Admin Management)
    Route::group(['prefix' => 'attendance/timesheets', 'as' => 'attendance.timesheets.'], function() {
        Route::post('bulk', [\App\Http\Controllers\Admin\TimesheetController::class, 'bulkStore'])->name('bulk');
        Route::get('weekly-log', [\App\Http\Controllers\Admin\TimesheetController::class, 'getWeeklyLog'])->name('weekly-log');
    });
    Route::apiResource('attendance/timesheets', \App\Http\Controllers\Admin\TimesheetController::class)->names('attendance.timesheets');

    // User & Employee Management
    Route::apiResource('users', \App\Http\Controllers\Admin\UserController::class);
    // Employee Options (Must be before apiResource('employees') to avoid wildcard collision)
    Route::get('employees/expense-options', [\App\Http\Controllers\Admin\EmployeeController::class, 'expenseOptions']); 

    Route::apiResource('employees', \App\Http\Controllers\Admin\EmployeeController::class);
    Route::get('access-review', [App\Http\Controllers\Admin\AccessReviewController::class, 'index'])->name('access-review.index');

    // Org Structure
    Route::apiResource('departments', DepartmentController::class);
    Route::apiResource('locations', LocationController::class);

    // --- LEAVE MANAGEMENT ---
    Route::apiResource('leave-types', \App\Http\Controllers\Admin\LeaveTypeController::class);
    Route::apiResource('holidays', \App\Http\Controllers\Admin\HolidayController::class); // Added
    // Manager/Admin Actions on Requests
    Route::get('leave-requests', [\App\Http\Controllers\Admin\LeaveRequestController::class, 'index'])->name('leave-requests.index');
    Route::post('leave-requests/{id}/approve', [\App\Http\Controllers\Admin\LeaveRequestController::class, 'approve'])->name('leave-requests.approve');
    Route::post('leave-requests/{id}/reject', [\App\Http\Controllers\Admin\LeaveRequestController::class, 'reject'])->name('leave-requests.reject');

    // --- ATTENDANCE MANAGEMENT ---
    Route::post('attendance/policies', [\App\Http\Controllers\Admin\AttendancePolicyController::class, 'updatePolicy'])->name('attendance.policies.update');
    Route::put('attendance/shifts/{id}', [\App\Http\Controllers\Admin\AttendancePolicyController::class, 'updateShift'])->name('attendance.shifts.update');
    
    // --- DOCUMENT MANAGEMENT ---
    Route::get('documents', [\App\Http\Controllers\Admin\AdminDocumentController::class, 'index'])->name('documents.index'); // Global view
    // Employee Management Actions
    Route::post('employees/{employee}/create-login', [\App\Http\Controllers\Admin\EmployeeController::class, 'createLogin'])->name('employees.create-login');
    Route::post('employees/{employee}/toggle-status', [\App\Http\Controllers\Admin\EmployeeController::class, 'toggleStatus'])->name('employees.toggle-status');
    Route::post('employees/{employee}/reset-password', [\App\Http\Controllers\Admin\EmployeeController::class, 'resetPassword'])->name('employees.reset-password');

    // Employee Documents (Re-added)
    Route::get('employees/{employee}/documents', [\App\Http\Controllers\Admin\EmployeeDocumentController::class, 'index'])->name('employees.documents.index');
    Route::post('employees/{employee}/documents', [\App\Http\Controllers\Admin\EmployeeDocumentController::class, 'store'])->name('employees.documents.store');
    Route::delete('employees/{employee}/documents/{document}', [\App\Http\Controllers\Admin\EmployeeDocumentController::class, 'destroy'])->name('employees.documents.destroy');
    Route::post('employees/{employee}/documents/{document}/verify', [\App\Http\Controllers\Admin\EmployeeDocumentController::class, 'verify'])->name('employees.documents.verify');

    // Employee Family (Admin Management)
    Route::get('employees/{employee}/families', [\App\Http\Controllers\Admin\EmployeeFamilyController::class, 'index'])->name('employees.families.index');
    Route::post('employees/{employee}/families', [\App\Http\Controllers\Admin\EmployeeFamilyController::class, 'store'])->name('employees.families.store');
    Route::put('employees/{employee}/families/{family}', [\App\Http\Controllers\Admin\EmployeeFamilyController::class, 'update'])->name('employees.families.update');
    Route::delete('employees/{employee}/families/{family}', [\App\Http\Controllers\Admin\EmployeeFamilyController::class, 'destroy'])->name('employees.families.destroy');

    // Employee Details Updates (Personal, Health, Bank)
    Route::put('employees/{employee}/personal', [\App\Http\Controllers\Admin\EmployeeController::class, 'updatePersonal'])->name('employees.personal.update');
    Route::put('employees/{employee}/health', [\App\Http\Controllers\Admin\EmployeeController::class, 'updateHealth'])->name('employees.health.update');
    Route::put('employees/{employee}/bank', [\App\Http\Controllers\Admin\EmployeeController::class, 'updateBank'])->name('employees.bank.update');
    
    Route::get('employees/{employee}/expenses', [\App\Http\Controllers\Admin\EmployeeController::class, 'expenses'])->name('employees.expenses.index');
    Route::post('employees/{employee}/expenses', [\App\Http\Controllers\Admin\EmployeeController::class, 'storeExpense'])->name('employees.expenses.store');

});

// --- EMPLOYEE SELF-SERVICE API ---
Route::middleware(['auth:sanctum'])->prefix('employee')->group(function () {
    // Attendance Actions
    Route::post('attendance/clock-in', [\App\Http\Controllers\Employee\AttendanceController::class, 'clockIn']);
    Route::post('attendance/clock-out', [\App\Http\Controllers\Employee\AttendanceController::class, 'clockOut']);
    
    // Save Custom Dashboard Layout
    Route::post('dashboard/layout', [\App\Http\Controllers\DashboardController::class, 'saveLayout']);
    
    // Regularization
    Route::post('attendance/regularize', [\App\Http\Controllers\Employee\RegularizationController::class, 'store']);

    // Swaps
    Route::get('attendance/swaps', [\App\Http\Controllers\Employee\ShiftSwapController::class, 'index']);
    Route::post('attendance/swaps', [\App\Http\Controllers\Employee\ShiftSwapController::class, 'store']);
    Route::put('attendance/swaps/{id}', [\App\Http\Controllers\Employee\ShiftSwapController::class, 'update']);
    
    // Floating Holidays
    Route::get('attendance/floating-holidays', [\App\Http\Controllers\Employee\FloatingHolidayController::class, 'index']);
    Route::post('attendance/floating-holidays', [\App\Http\Controllers\Employee\FloatingHolidayController::class, 'store']);

    // Timesheets
    Route::group(['prefix' => 'attendance/timesheets', 'as' => 'attendance.timesheets.', 'controller' => \App\Http\Controllers\Employee\TimesheetController::class], function() {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');
        Route::get('assigned-tasks', 'getAssignedTasks')->name('assigned-tasks');
        Route::get('project-tasks/{id}', 'getProjectTasks')->name('project-tasks');
        Route::get('weekly-log', 'getWeeklyLog')->name('weekly-log');
        Route::post('bulk', 'bulkStore')->name('bulk');
        
        Route::group(['prefix' => '{timesheet}', 'as' => 'single.'], function() {
            Route::put('/', 'update')->name('update');
            Route::delete('/', 'destroy')->name('destroy');
            Route::post('submit', 'submit')->name('submit');
        });
    });

    // Holiday View
    Route::get('leave/holidays', [\App\Http\Controllers\Employee\HolidayController::class, 'index']);

    // Family
    Route::get('family', [\App\Http\Controllers\Admin\EmployeeFamilyController::class, 'index']); // Self
    Route::post('family', [\App\Http\Controllers\Admin\EmployeeFamilyController::class, 'store']);
    Route::put('family/{family}', [\App\Http\Controllers\Admin\EmployeeFamilyController::class, 'update']);
    Route::delete('family/{family}', [\App\Http\Controllers\Admin\EmployeeFamilyController::class, 'destroy']);
    // Dashboard Widgets (Async Performance Layer)
    Route::prefix('dashboard')->group(function () {
        Route::get('widgets/attendance', [\App\Http\Controllers\DashboardController::class, 'getAttendanceWidget']);
        Route::get('widgets/pulse', [\App\Http\Controllers\DashboardController::class, 'getPulseWidget']);
        Route::get('widgets/payslips', [\App\Http\Controllers\DashboardController::class, 'getPayslipsWidget']);
        Route::get('widgets/leave-balances', [\App\Http\Controllers\DashboardController::class, 'getLeaveBalances']);
        Route::post('layout', [\App\Http\Controllers\DashboardLayoutController::class, 'saveLayout']);
    });
});

// --- MANAGER API ---
Route::middleware(['auth:sanctum', 'role:Manager|Admin'])->prefix('manager')->group(function () {
    Route::get('approvals', [\App\Http\Controllers\Manager\ApprovalController::class, 'index']);
    Route::post('approvals/action', [\App\Http\Controllers\Manager\ApprovalController::class, 'action']);
});

// --- CRM API ---
Route::middleware(['auth:sanctum'])->prefix('meetings')->group(function () {
    Route::get('/analytics', [\App\Http\Controllers\CRM\MeetingController::class, 'analytics']);
    Route::post('/{meeting}/reschedule', [\App\Http\Controllers\CRM\MeetingController::class, 'reschedule']);
});

Route::middleware('auth:sanctum')->get('navigation', [\App\Http\Controllers\Api\NavigationController::class, 'index']);

// Test Email Route
Route::get('test-email', function (\App\Services\Email\EmailService $emailService) {
    try {
        $emailService->send([
            'from' => 'HRMS System <noreply@KNR Office.com>',
            'to' => ['delivered@resend.dev'],
            'subject' => 'Test Email from HRMS',
            'html' => '<p>This is a test email from the new EmailService.</p>'
        ]);
        return response()->json(['message' => 'Email sent successfully']);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
});

// --- HARDWARE INTEGRATION ---
Route::post('biometrics/push', [\App\Http\Controllers\Api\BiometricController::class, 'push']);

// --- MOBILE V1 API ---
Route::prefix('mobile/v1')->name('api.mobile.v1.')->group(function () {
    // Public Routes
    Route::post('login', [\App\Http\Controllers\Api\Mobile\V1\AuthController::class, 'login']);

    // Protected Routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [\App\Http\Controllers\Api\Mobile\V1\AuthController::class, 'logout']);
        Route::get('me', [\App\Http\Controllers\Api\Mobile\V1\AuthController::class, 'me']);

        // Attendance
        Route::get('attendance', [\App\Http\Controllers\Api\Mobile\V1\AttendanceController::class, 'index']);
        Route::post('attendance/clock-in', [\App\Http\Controllers\Api\Mobile\V1\AttendanceController::class, 'clockIn']);
        Route::post('attendance/clock-out', [\App\Http\Controllers\Api\Mobile\V1\AttendanceController::class, 'clockOut']);
        Route::post('attendance/sync', [\App\Http\Controllers\Api\Mobile\V1\AttendanceController::class, 'sync']);

        // Tasks
        Route::get('tasks', [\App\Http\Controllers\Api\Mobile\V1\TaskController::class, 'index']);
        Route::get('tasks/{task}', [\App\Http\Controllers\Api\Mobile\V1\TaskController::class, 'show']);
        Route::put('tasks/{task}/progress', [\App\Http\Controllers\Api\Mobile\V1\TaskController::class, 'updateProgress']);
        Route::post('checklist/{checklist}/toggle', [\App\Http\Controllers\Api\Mobile\V1\TaskController::class, 'toggleChecklist']);

        // Leave & WFH
        Route::get('leave', [\App\Http\Controllers\Api\Mobile\V1\LeaveController::class, 'index']);
        Route::get('leave/types', [\App\Http\Controllers\Api\Mobile\V1\LeaveController::class, 'getTypes']);
        Route::post('leave/apply', [\App\Http\Controllers\Api\Mobile\V1\LeaveController::class, 'store']);
        Route::post('wfh/apply', [\App\Http\Controllers\Api\Mobile\V1\LeaveController::class, 'store']);

        // Bug Tracker
        Route::get('bugs', [\App\Http\Controllers\Api\Mobile\V1\BugTrackerController::class, 'index']);
        Route::post('bugs/close-all', [\App\Http\Controllers\Api\Mobile\V1\BugTrackerController::class, 'closeAll']);
        Route::put('bugs/{bug}/status', [\App\Http\Controllers\Api\Mobile\V1\BugTrackerController::class, 'updateStatus']);

        // Email Hub & Conversations
        Route::get('/emails', [\App\Http\Controllers\Api\Mobile\V1\EmailHubController::class, 'index']);
        Route::get('/emails/signals', [\App\Http\Controllers\Api\Mobile\V1\EmailHubController::class, 'signals']);
        Route::get('/emails/{thread}', [\App\Http\Controllers\Api\Mobile\V1\EmailHubController::class, 'show']);
        Route::post('/emails/reply', [\App\Http\Controllers\Api\Mobile\V1\EmailHubController::class, 'reply']);
        Route::post('/emails/compose', [\App\Http\Controllers\Api\Mobile\V1\EmailHubController::class, 'compose']);

        // Email Accounts (SMTP/IMAP Mapping)
        Route::get('/email-accounts', [\App\Http\Controllers\Api\Mobile\V1\EmailAccountController::class, 'index']);
        Route::post('/email-accounts', [\App\Http\Controllers\Api\Mobile\V1\EmailAccountController::class, 'store']);
        Route::post('/email-accounts/{account}/toggle', [\App\Http\Controllers\Api\Mobile\V1\EmailAccountController::class, 'toggle']);

        // Notifications
        Route::get('/notifications', [\App\Http\Controllers\Api\Mobile\V1\NotificationController::class, 'index']);
        Route::get('/notifications/unread-count', [\App\Http\Controllers\Api\Mobile\V1\NotificationController::class, 'unreadCount']);
        Route::post('/notifications/{id}/read', [\App\Http\Controllers\Api\Mobile\V1\NotificationController::class, 'markRead']);

        // Timesheets
        Route::get('/timesheets', [\App\Http\Controllers\Api\Mobile\V1\TimesheetController::class, 'index']);
        Route::post('/timesheets', [\App\Http\Controllers\Api\Mobile\V1\TimesheetController::class, 'store']);
        
        // Approvals (reusing manager logic but via mobile)
        Route::get('approvals', [\App\Http\Controllers\Manager\ApprovalController::class, 'index']);
        Route::post('approvals/action', [\App\Http\Controllers\Manager\ApprovalController::class, 'action']);

        // Payslips
        Route::get('payslips', [\App\Http\Controllers\Api\Mobile\V1\PayslipController::class, 'index']);
        Route::get('payslips/download', [\App\Http\Controllers\Api\Mobile\V1\PayslipController::class, 'download']);
    });
});

// Ghost Asset Detection (Public Pulse - Protected by API Key in real world)
Route::post('/agent/pulse', [App\Http\Controllers\Api\GhostAssetController::class, 'pulse']);