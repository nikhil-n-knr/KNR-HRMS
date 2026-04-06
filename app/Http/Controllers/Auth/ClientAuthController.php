<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use App\Models\ClientUser;

class ClientAuthController extends Controller
{
    public function showLogin()
    {
        return \Inertia\Inertia::render('Auth/ClientLogin');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $user = ClientUser::where('email', $request->email)->first();

        if ($user && !$user->is_active) {
            throw ValidationException::withMessages([
                'email' => ['Your access has been revoked. Please contact your account manager.'],
            ]);
        }

        if (Auth::guard('client')->attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user->update([
                'last_login_at' => now(),
            ]);

            return redirect()->intended(route('client.dashboard'));
        }

        throw ValidationException::withMessages([
            'email' => ['The provided credentials do not match our records.'],
        ]);
    }

    public function logout(Request $request)
    {
        Auth::guard('client')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('client.login');
    }
}
