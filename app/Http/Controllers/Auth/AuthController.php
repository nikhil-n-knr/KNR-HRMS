<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use App\Services\Security\OtpService;
use App\Services\Communication\NotificationService;

class AuthController extends Controller
{
    /**
     * Handle an incoming authentication request.
     */
    protected $logger;

    public function __construct(\App\Services\Infrastructure\ActivityLogger $logger) 
    {
        $this->logger = $logger;
    }

    /**
     * Handle an incoming authentication request.
     */
    public function login(Request $request, \App\Services\Infrastructure\ActivityLogger $activityLogger)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $user = \App\Models\User::where('email', $request->email)->first();

        // 1. Check Lockout
        if ($user && $user->locked_until && now()->lessThan($user->locked_until)) {
             $activityLogger->log('login_lockout', $user, ['email' => $request->email], 'auth');
             throw ValidationException::withMessages([
                'email' => ['Your account is locked. Please try again later.'],
            ]);
        }

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            
            // 2. Audit Success
            $user->update([
                'last_login_at' => now(),
                'failed_login_attempts' => 0,
                'locked_until' => null
            ]);
            
            $activityLogger->log('login', $user, [
                'email' => $request->email,
                'status' => 'success',
                'device_details' => request()->header('User-Agent'), // Redundant but explicit as requested
            ], 'auth');

            return response()->json(['message' => 'Logged in successfully']);
        }

        // 3. Audit Failure
        if ($user) {
            $user->increment('failed_login_attempts');
            // Lockout policy: 5 attempts -> 15 mins lock
            if ($user->failed_login_attempts >= 5) {
                $user->update(['locked_until' => now()->addMinutes(15)]);
            }
        }

        $activityLogger->log('login_failed', $user, [
            'email' => $request->email,
            'ip' => $request->ip(), // Explicit request
            'user_agent' => $request->header('User-Agent'),
            'input_data' => $request->except(['password', '_token']), // Log body minus secrets
        ], 'auth');

        throw ValidationException::withMessages([
            'email' => ['The provided credentials do not match our records.'],
        ]);
    }

    /**
     * Destroy an authenticated session.
     */
    public function logout(Request $request, \App\Services\Infrastructure\ActivityLogger $activityLogger)
    {
        $user = Auth::user();
        if ($user) {
             $activityLogger->log('logout', $user, ['email' => $user->email], 'auth');
        }

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return response()->json(['message' => 'Logged out successfully']);
    }

    public function impersonate(Request $request, \App\Services\Auth\ImpersonationService $impersonationService)
    {
        $request->validate(['user_id' => 'required|exists:users,id']);
        
        $targetUser = \App\Models\User::with('roles')->findOrFail($request->user_id);
        
        try {
            // For session-based auth (Sanctum stateful), we can use `login` directly
            // But we must verify CanImpersonate first
            if (!$impersonationService->canImpersonate(auth()->user(), $targetUser)) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            // Stateful login switch
            Auth::guard('web')->login($targetUser);
            $request->session()->regenerate();

            return response()->json(['message' => "Impersonating {$targetUser->name}"]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 403);
        }
    }

    public function forgotPassword(Request $request, OtpService $otpService)
    {
        // 1. User Enumeration Protection: Remove 'exists:users,email'
        // Validation will pass even if email is random.
        $request->validate(['email' => 'required|email']);
        
        $user = \App\Models\User::where('email', $request->email)->first();
        
        // Only generate and send if user exists, but ALWAYS return success
        if ($user) {
            $otp = $otpService->generate($request->email);
            
            // 2. Centralized Templates
            $template = \App\Services\Communication\NotificationTemplates::get('forgot_password', ['otp' => $otp]);
            
            // 3. Queueing
            \App\Jobs\SendNotificationJob::dispatch(
                'email', // Use email channel (handled by EmailService)
                $request->email, 
                $template['subject'], 
                $template['content'],
                ['type' => 'forgot_password']
            );
        }
        
        // Generic Response
        return response()->json(['message' => 'If an account matches that email, we have sent an OTP.']);
    }

    public function verifyOtp(Request $request, OtpService $otpService)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required'
        ]);
        
        // Use non-destructive check
        $valid = $otpService->check($request->email, $request->otp);
        
        if (!$valid) {
            return response()->json(['message' => 'Invalid or expired OTP.'], 400);
        }
        
        return response()->json(['message' => 'OTP verified.']);
    }

    public function resetPassword(Request $request, OtpService $otpService)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'otp' => 'required',
            'password' => 'required|confirmed|min:8'
        ]);
        
        // Validate and Consume
        if (!$otpService->validate($request->email, $request->otp)) {
             return response()->json(['message' => 'Invalid or expired OTP.'], 400);
        }

        $user = \App\Models\User::where('email', $request->email)->first();
        $user->password = \Illuminate\Support\Facades\Hash::make($request->password);
        $user->save();
        
        return response()->json(['message' => 'Password reset successfully.']);
    }
}
