<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Services\Attendance\AttendanceRegistryService;
use App\Models\AttendanceLog;
use App\Models\BiometricDevice;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

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
        $employee = $user->getEmployeeProfile();

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

            $todayLog = $this->autoCheckoutIfShiftEnded($employee, $todayLog);

            // Fetch monthly history (paginated)
            $history = AttendanceLog::with('sessions')->where('employee_id', $employee->id)
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
        $employee = auth()->user()->getEmployeeProfile();
        
        try {
            $log = $this->registry->clockIn($employee, $request->ip());
            $log = AttendanceLog::with('sessions')->find($log->id);

            if ($request->wantsJson()) {
                return response()->json(['message' => 'Clocked In Successfully marked as Present', 'success' => true, 'log' => $log]);
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
        $employee = auth()->user()->getEmployeeProfile();

        try {
            $log = $this->registry->clockOut($employee, $request->ip());
            $log = AttendanceLog::with('sessions')->find($log->id);
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Clocked Out Successfully', 'success' => true, 'log' => $log]);
            }
            return back()->with('success', 'Clocked Out Successfully');
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(['message' => $e->getMessage(), 'success' => false], 422);
            }
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Auto Check-In based on Office WiFi IP.
     */
    public function autoCheckIn(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated request.'], 401);
        }

        $candidateIps = $this->resolveCandidateIps($request);
        $tenantId = $user->tenant_id;

        $officeWifiIps = BiometricDevice::query()
            ->where('is_office_wifi', true)
            ->where(function ($q) use ($tenantId) {
                $q->where('tenant_id', $tenantId)->orWhereNull('tenant_id');
            })
            ->pluck('ip_address')
            ->filter()
            ->values();

        $matchedIp = collect($candidateIps)->first(function ($ip) use ($officeWifiIps) {
            return $officeWifiIps->contains($ip);
        });

        if (!$matchedIp) {
            Log::warning('Auto check-in rejected: unauthorized network', [
                'user_id' => $user->id,
                'tenant_id' => $tenantId,
                'request_ip' => $request->ip(),
                'candidate_ips' => $candidateIps,
                'configured_office_ips' => $officeWifiIps->all(),
            ]);

            return response()->json([
                'success' => false, 
                'message' => 'Unauthorized Network: No office WiFi IP match found for your current connection.',
                'diagnostics' => [
                    'detected_ips' => $candidateIps,
                    'configured_office_ips' => $officeWifiIps,
                ],
            ], 403);
        }

        $employee = $user->getEmployeeProfile();
        if (!$employee) {
            $employee = $this->provisionEmployeeProfileForAutoCheckIn($user);
        }

        if (!$employee) {
            Log::warning('Auto check-in blocked: employee profile missing', [
                'user_id' => $user->id,
                'tenant_id' => $tenantId,
                'matched_ip' => $matchedIp,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Your network is authorized, but your account is not linked to an employee profile. Please contact HR/admin.',
            ], 422);
        }

        try {
            $log = $this->registry->clockIn($employee, $matchedIp, 'WiFi');
            $log = AttendanceLog::with('sessions')->find($log->id);
            return response()->json([
                'success' => true, 
                'message' => 'Auto Check-In Successful. Welcome to the office!',
                'log' => $log
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    private function resolveCandidateIps(Request $request): array
    {
        $ips = [];

        $ips[] = $this->normalizeIp($request->ip());

        $forwardedFor = $request->header('X-Forwarded-For');
        if (!empty($forwardedFor)) {
            $parts = array_map('trim', explode(',', $forwardedFor));
            foreach ($parts as $part) {
                $ips[] = $this->normalizeIp($part);
            }
        }

        $cfConnectingIp = $request->header('CF-Connecting-IP');
        if (!empty($cfConnectingIp)) {
            $ips[] = $this->normalizeIp($cfConnectingIp);
        }

        $isLoopback = collect($ips)->contains(fn ($ip) => in_array($ip, ['127.0.0.1', '::1'], true));
        if ($isLoopback) {
            $ips[] = '127.0.0.1';
            $ips[] = '::1';
            try {
                $publicIp = Http::timeout(2)->get('https://api.ipify.org?format=json')->json('ip');
                if (!empty($publicIp)) {
                    $ips[] = $this->normalizeIp($publicIp);
                }
            } catch (\Throwable $e) {
                Log::info('Auto check-in public IP fallback failed', ['error' => $e->getMessage()]);
            }
        }

        return collect($ips)
            ->filter(fn ($ip) => filter_var($ip, FILTER_VALIDATE_IP))
            ->unique()
            ->values()
            ->all();
    }

    private function normalizeIp(?string $ip): ?string
    {
        if (empty($ip)) {
            return null;
        }

        if (str_starts_with($ip, '::ffff:')) {
            return substr($ip, 7);
        }

        return trim($ip);
    }

    private function provisionEmployeeProfileForAutoCheckIn($user): ?Employee
    {
        try {
            $existing = Employee::where('user_id', $user->id)->first();
            if ($existing) {
                return $existing;
            }

            if (!empty($user->email)) {
                $emailMatch = Employee::where('email', $user->email)->whereNull('user_id')->first();
                if ($emailMatch) {
                    $emailMatch->update(['user_id' => $user->id]);
                    return $emailMatch;
                }
            }

            $name = trim((string) ($user->name ?? 'Auto User'));
            $parts = preg_split('/\s+/', $name, 2);
            $firstName = $parts[0] ?? 'Auto';
            $lastName = $parts[1] ?? 'User';

            $baseCode = 'AUTO-' . str_pad((string) $user->id, 5, '0', STR_PAD_LEFT);
            $employeeCode = $baseCode;
            $suffix = 1;
            while (Employee::where('employee_code', $employeeCode)->exists()) {
                $employeeCode = $baseCode . '-' . $suffix;
                $suffix++;
            }

            $employee = Employee::create([
                'uuid' => (string) Str::uuid(),
                'tenant_id' => $user->tenant_id ?? 1,
                'user_id' => $user->id,
                'employee_code' => $employeeCode,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $user->email,
                'phone' => $user->mobile,
                'location_id' => $user->location_id,
                'status' => 'active',
                'employment_type' => 'full_time',
            ]);

            Log::warning('Auto-created employee profile for auto check-in', [
                'user_id' => $user->id,
                'employee_id' => $employee->id,
                'employee_code' => $employee->employee_code,
            ]);

            return $employee;
        } catch (\Throwable $e) {
            Log::error('Auto employee profile provisioning failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    private function autoCheckoutIfShiftEnded(Employee $employee, ?AttendanceLog $log): ?AttendanceLog
    {
        if (!$log) {
            return null;
        }

        $this->registry->autoCheckoutOpenSessions(Carbon::now(), $employee->id);
        return AttendanceLog::with('sessions', 'shift')->find($log->id);
    }

    /**
     * Get today's attendance status summary.
     */
    public function todayStatus(Request $request)
    {
        $employee = auth()->user()->getEmployeeProfile();
        if (!$employee) {
            return response()->json(['log' => null, 'ip' => $request->ip()]);
        }

        $date = Carbon::today();
        $log = AttendanceLog::with('sessions', 'shift')->where('employee_id', $employee->id)
            ->where('date', $date)
            ->first();

        $log = $this->autoCheckoutIfShiftEnded($employee, $log);
        
        $shift = $this->registry->getShiftForDate($employee, $date);

        return response()->json([
            'log' => $log,
            'ip' => $request->ip(),
            'shift' => $shift ? $shift->name : 'N/A',
            'zone' => $employee->location?->name ?? 'Headquarters'
        ]);
    }
}
