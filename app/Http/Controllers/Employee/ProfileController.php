<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use App\Services\Infrastructure\LoggerService;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /**
     * Display the authenticated employee profile via UUID.
     */
    public function show(Request $request, string $uuid): Response
    {
        $user = $request->user();
        $viewerEmployee = $user->employee;

        $employee = Employee::with([
            'department',
            'location',
            'user.roles',
            'manager',
            'personalDetail',
            'healthRecord',
            'families',
            'bankDetails',
            'latestSalary.structure',
        ])->where('uuid', $uuid)->firstOrFail();

        $isSuperAdmin = $user->hasRole('Super Admin');

        if (!$isSuperAdmin && (!$viewerEmployee || $viewerEmployee->uuid !== $employee->uuid)) {
            abort(403, 'Unauthorized access to this operative sanctuary.');
        }

        // Fetch Payslip History (Last 12 published)
        $payslips = \App\Models\Payslip::where('employee_id', $employee->id)
            ->whereHas('payroll', function($q) {
                $q->whereIn('status', ['Published', 'Paid']); // Support both for backward compatibility
            })
            ->with(['payroll:id,month,year,status']) // Eager load minimal payroll data
            ->orderBy('id', 'desc')
            ->limit(12)
            ->get()
            ->map(function($slip) {
                return [
                    'id' => $slip->id,
                    'month_label' => \Carbon\Carbon::createFromDate($slip->payroll->year, $slip->payroll->month, 1)->format('M Y'),
                    'gross_pay' => $slip->gross_earnings,
                    'net_pay' => $slip->net_pay,
                    'status' => $slip->status == 'Paid' || $slip->status == 'Published' ? 'Paid' : $slip->status,
                    'download_url' => route('employee.payslips.download', $slip->id)
                ];
            });

        return Inertia::render('Employee/EmployeeProfile', [
            'employee' => $employee,
            'payslips' => $payslips, // Injected
            'history' => \Illuminate\Support\Facades\DB::table('activity_logs')
                ->where('subject_id', $employee->id)
                ->where('subject_type', 'like', '%Employee%')
                ->orderByDesc('created_at')
                ->limit(20)
                ->get()
                ->map(function ($log) {
                    return [
                        'id' => $log->id,
                        'description' => $log->description,
                        'created_at' => $log->created_at,
                        'properties' => json_decode($log->properties, true)
                    ];
                }),
            'tab' => $request->query('tab', 'personal'),
            'canManage' => false,
        ]);
    }

    /**
     * Display the personal hub for the authenticated employee.
     */
    public function hub(Request $request): Response|\Illuminate\Http\RedirectResponse
    {
        $user = $request->user();
        $employee = $user->employee;

        if (!$employee) {
            // If it's a mobile request, we stay in mobile land. But this is a web route.
            // For web, if no employee record, redirect to dashboard.
            return redirect()->route('dashboard')->with('error', 'Employee record not found. Accessing standard dashboard.');
        }

        return Inertia::render('Employee/PersonalHub', [
            'employee' => $employee->load(['department', 'location']),
            'auth' => [
                'user' => $user,
                'profileUrl' => route('employee.profile', $employee->uuid)
            ]
        ]);
    }

    /**
     * Handle profile picture upload.
     */
    public function updateAvatar(Request $request, string $uuid, LoggerService $logger)
    {
        $employee = Employee::where('uuid', $uuid)->firstOrFail();
        
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        try {
            if ($request->hasFile('avatar')) {
                // Delete old avatar if exists
                if ($employee->avatar) {
                    Storage::disk('public')->delete($employee->avatar);
                }

                $file = $request->file('avatar');
                $filename = 'avatars/' . $employee->uuid . '_' . time() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('', $filename, 'public');

                $employee->update(['avatar' => $path]);

                $logger->log(
                    'employee_profile',
                    'AVATAR_UPDATE',
                    "Profile picture updated for employee: {$employee->name}",
                    ['employee_id' => $employee->id, 'path' => $path],
                    'info'
                );

                return redirect()->back()->with('success', 'Profile picture updated successfully!')->setStatusCode(303);
            }
        } catch (\Exception $e) {
            $logger->log(
                'employee_profile',
                'AVATAR_UPDATE_FAILED',
                "Failed to update profile picture for employee: {$employee->name}",
                ['employee_id' => $employee->id, 'error' => $e->getMessage()],
                'error'
            );

            return redirect()->back()->with('error', 'Failed to update profile picture.')->setStatusCode(303);
        }

        return redirect()->back()->setStatusCode(303);
    }

    /**
     * Stream avatar image.
     */
    public function streamAvatar(string $uuid)
    {
        $employee = Employee::where('uuid', $uuid)->firstOrFail();
        
        if (!$employee->avatar || !Storage::disk('public')->exists($employee->avatar)) {
            // Fallback to a placeholder or 404
            abort(404);
        }

        $path = Storage::disk('public')->path($employee->avatar);

        return response()->file($path, [
            'Cache-Control' => 'public, max-age=86400',
            'Content-Type' => Storage::disk('public')->mimeType($employee->avatar),
        ]);
    }
}
