<?php

namespace App\Http\Controllers\ClientPortal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ClientLoginController extends Controller
{
    public function showLoginForm()
    {
        return Inertia::render('ClientPortal/Auth/Login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::guard('client')->attempt(['email' => $request->email, 'password' => $request->password, 'is_active' => true])) {
            $request->session()->regenerate();
            
            // Update last login
            $user = Auth::guard('client')->user();
            $user->update(['last_login_at' => now()]);

            return redirect()->intended(route('portal.dashboard'));
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records, or your account is deactivated.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::guard('client')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('portal.login');
    }
}
