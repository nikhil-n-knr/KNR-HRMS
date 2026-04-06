<?php

namespace App\Http\Controllers\LMS;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;

class LmsAuthController extends Controller
{
    /**
     * Display the login view.
     */
    public function showLogin()
    {
        return Inertia::render('LMS/Public/Login', [
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            return redirect()->intended(route('lms.store.dashboard'));
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    /**
     * Display the registration view.
     */
    public function showRegister()
    {
        return Inertia::render('LMS/Public/Register');
    }

    /**
     * Handle an incoming registration request.
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'status' => 'active',
        ]);

        // Assign Student role (create if not exists for demo safety)
        $role = Role::firstOrCreate(['name' => 'Student']);
        $user->roles()->syncWithoutDetaching([
            $role->id => [
                'assigned_by' => 1,
                'valid_from' => now(),
                'is_active' => true
            ]
        ]);

        Auth::login($user);

        return redirect(route('lms.store.dashboard'));
    }

    /**
     * Display the forgot password view.
     */
    public function showForgotPassword()
    {
        return Inertia::render('LMS/Public/ForgotPassword', [
            'status' => session('status'),
        ]);
    }

    /**
     * Handle forgot password request.
     */
    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        // Mock for now: In production, use standard Laravel password reset logic
        return back()->with('status', 'We have emailed your password reset link!');
    }

    /**
     * Destroy an authenticated session.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect(route('lms.store.catalog'));
    }
}
